<?php

namespace App\Http\Controllers;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use App\Models\UserReservation;
use Carbon\Carbon;

class WeTravelController extends Controller
{
  private $refresh_token;
  private $access_token;
  private $api_base_url;
  private $token_endpoint;
  private $payment_links_endpoint;

  public function __construct()
  {
    $this->refresh_token = config('services.wetravel.refresh_token');
    $this->api_base_url = config('services.wetravel.api_base_url');
    $this->token_endpoint = $this->api_base_url . '/auth/tokens/access';
    $this->payment_links_endpoint = $this->api_base_url . '/payment_links';
  }

  /**
   * Get access token using refresh token
   * @return bool|string
   */
  private function getAccessToken()
  {
    try {
      Log::channel('wetravel')->info('Requesting access token from WeTravel');

      $response = Http::withHeaders([
        'Authorization' => 'Bearer ' . $this->refresh_token,
        'Content-Type' => 'application/json',
        'Accept' => 'application/json',
      ])->post($this->token_endpoint);

      if ($response->successful()) {
        $data = $response->json();
        Log::channel('wetravel')->info('Access token obtained successfully', ['data' => $data]);

        // WeTravel returns the token directly in the response
        $this->access_token = $data['access_token'] ?? $data['data']['access_token'] ?? null;

        if (!$this->access_token) {
          Log::channel('wetravel')->error('No access token in response', ['response' => $data]);
          return false;
        }

        return $this->access_token;
      } else {
        Log::channel('wetravel')->error('Failed to get access token', [
          'status' => $response->status(),
          'body' => $response->body()
        ]);
        return false;
      }
    } catch (Exception $e) {
      Log::channel('wetravel')->error('Exception getting access token', [
        'message' => $e->getMessage(),
        'line' => $e->getLine()
      ]);
      return false;
    }
  }

  /**
   * Create a payment link for a reservation
   * 
   * @param Request $request
   * @return \Illuminate\Http\JsonResponse
   */
  public function createPaymentLink(Request $request)
  {
    try {
      $request->validate([
        'reservation_id' => 'required|exists:user_reservations,reservation_number',
        'external_reference' => 'required|string',
        'title' => 'required|string',
        'start_date' => 'required|date_format:Y-m-d',
        'end_date' => 'nullable|date_format:Y-m-d|after_or_equal:start_date',
        'amount' => 'required|numeric|min:0.01',
        'currency' => 'required|string|in:USD,ARS,EUR',
        'payer_name' => 'required|string',
        'payer_email' => 'required|email',
        'return_url' => 'required|url',
      ]);

      // Get access token
      $access_token = $this->getAccessToken();
      if (!$access_token) {
        return response()->json([
          'success' => false,
          'message' => 'Failed to authenticate with WeTravel'
        ], 401);
      }

      // Use same date for end_date if not provided (single day excursion)
      $end_date = $request->end_date ?? $request->start_date;

      // Prepare payment link payload according to WeTravel API spec
      $payload = [
        'data' => [
          'trip' => [
            'title' => $request->title,
            'start_date' => $request->start_date,
            'end_date' => $end_date,
            'currency' => $request->currency,
            'participant_fees' => 'all'
          ],
          'pricing' => [
            'price' => (float)$request->amount
          ],
          'buyer_email' => $request->payer_email,
          'buyer_name' => $request->payer_name,
          'return_url' => $request->return_url,
          'external_reference' => $request->external_reference,
          'metadata' => [
            'reservation_number' => $request->reservation_id,
          ]
        ]
      ];

      Log::channel('wetravel')->info('Creating payment link', ['payload' => $payload]);

      // Create payment link
      $response = Http::withHeaders([
        'Authorization' => 'Bearer ' . $access_token,
        'Content-Type' => 'application/json',
        'Accept' => 'application/json',
      ])->post($this->payment_links_endpoint, $payload);

      if ($response->successful()) {
        $data = $response->json();
        Log::channel('wetravel')->info('Payment link created successfully', ['response' => $data]);

        // Extract payment link ID and URL from trip object
        $payment_link_id = $data['data']['trip']['uuid'] ?? null;
        $payment_link_url = $data['data']['trip']['url'] ?? null;

        if (!$payment_link_id || !$payment_link_url) {
          Log::channel('wetravel')->error('Missing payment link data', ['response' => $data]);
          return response()->json([
            'success' => false,
            'message' => 'Invalid response from WeTravel'
          ], 500);
        }

        // Update reservation with payment information
        $reservation = UserReservation::where('reservation_number', $request->reservation_id)->first();
        $reservation->payment_id = $payment_link_id;
        $reservation->payment_method = 'wetravel';
        $reservation->payment_status = 'pending';
        $reservation->save();

        return response()->json([
          'success' => true,
          'payment_link_id' => $payment_link_id,
          'payment_link_url' => $payment_link_url,
          'message' => 'Payment link created successfully'
        ], 201);
      } else {
        Log::channel('wetravel')->error('Failed to create payment link', [
          'status' => $response->status(),
          'body' => $response->body()
        ]);
        return response()->json([
          'success' => false,
          'message' => 'Failed to create payment link',
          'error' => $response->json()
        ], $response->status());
      }
    } catch (Exception $e) {
      Log::channel('wetravel')->error('Exception creating payment link', [
        'message' => $e->getMessage(),
        'line' => $e->getLine(),
        'trace' => $e->getTraceAsString()
      ]);

      return response()->json([
        'success' => false,
        'message' => 'Error creating payment link: ' . $e->getMessage()
      ], 500);
    }
  }

  /**
   * Get payment link status
   * 
   * @param Request $request
   * @return \Illuminate\Http\JsonResponse
   */
  public function getPaymentLinkStatus(Request $request)
  {
    try {
      $request->validate([
        'payment_link_id' => 'required|string'
      ]);

      // Get access token
      $access_token = $this->getAccessToken();
      if (!$access_token) {
        return response()->json([
          'success' => false,
          'message' => 'Failed to authenticate with WeTravel'
        ], 401);
      }

      $payment_link_id = $request->payment_link_id;
      $url = "{$this->payment_links_endpoint}/{$payment_link_id}";

      Log::channel('wetravel')->info('Fetching payment link status', ['url' => $url]);

      $response = Http::withHeaders([
        'Authorization' => 'Bearer ' . $access_token,
        'Accept' => 'application/json',
      ])->get($url);

      if ($response->successful()) {
        $data = $response->json();
        Log::channel('wetravel')->info('Payment link status retrieved', ['response' => $data]);

        $status = $data['data']['status'] ?? $data['status'] ?? 'unknown';
        $paid = $data['data']['paid'] ?? $data['paid'] ?? false;

        return response()->json([
          'success' => true,
          'payment_link_id' => $payment_link_id,
          'status' => $status,
          'is_paid' => $paid,
          'data' => $data
        ], 200);
      } else {
        Log::channel('wetravel')->error('Failed to get payment link status', [
          'status' => $response->status(),
          'body' => $response->body()
        ]);
        return response()->json([
          'success' => false,
          'message' => 'Failed to get payment link status'
        ], $response->status());
      }
    } catch (Exception $e) {
      Log::channel('wetravel')->error('Exception getting payment link status', [
        'message' => $e->getMessage(),
        'line' => $e->getLine()
      ]);

      return response()->json([
        'success' => false,
        'message' => 'Error: ' . $e->getMessage()
      ], 500);
    }
  }

  /**
   * Webhook handler for WeTravel payment notifications
   * 
   * @param Request $request
   * @return \Illuminate\Http\JsonResponse
   */
  public function webhookNotification(Request $request)
  {
    Log::channel('wetravel_webhook')->info('Webhook notification received', ['data' => $request->all()]);

    try {
      $data = $request->all();

      // Extract payment information
      $payment_link_id = $data['payment_link_id'] ?? $data['data']['id'] ?? null;
      $status = $data['status'] ?? $data['data']['status'] ?? null;
      $paid = $data['paid'] ?? $data['data']['paid'] ?? false;
      $metadata = $data['metadata'] ?? $data['data']['metadata'] ?? null;

      if (!$payment_link_id) {
        Log::channel('wetravel_webhook')->error('Webhook: No payment link ID found');
        return response()->json(['success' => false, 'message' => 'Missing payment link ID'], 400);
      }

      // Find reservation by payment ID
      $reservation = UserReservation::where('payment_id', $payment_link_id)
        ->where('payment_method', 'wetravel')
        ->first();

      if (!$reservation) {
        Log::channel('wetravel_webhook')->warning('Webhook: Reservation not found for payment link', [
          'payment_link_id' => $payment_link_id
        ]);
        return response()->json(['success' => false, 'message' => 'Reservation not found'], 404);
      }

      // Update reservation status based on payment status
      if ($paid && $status === 'completed') {
        $reservation->is_paid = true;
        $reservation->payment_status = 'completed';
        $reservation->save();

        // Store status in history
        UserReservation::store_user_reservation_status_history(
          $reservation->reservation_status_id,
          $reservation->id
        );

        Log::channel('wetravel_webhook')->info('Webhook: Payment completed', [
          'reservation_id' => $reservation->id,
          'payment_link_id' => $payment_link_id
        ]);
      } else if ($status === 'cancelled' || $status === 'failed') {
        $reservation->payment_status = $status === 'cancelled' ? 'cancelled' : 'failed';
        $reservation->save();

        Log::channel('wetravel_webhook')->warning('Webhook: Payment failed or cancelled', [
          'reservation_id' => $reservation->id,
          'status' => $status
        ]);
      }

      return response()->json(['success' => true, 'message' => 'Webhook processed'], 200);
    } catch (Exception $e) {
      Log::channel('wetravel_webhook')->error('Webhook exception', [
        'message' => $e->getMessage(),
        'line' => $e->getLine()
      ]);

      return response()->json([
        'success' => false,
        'message' => 'Error processing webhook: ' . $e->getMessage()
      ], 500);
    }
  }
}
