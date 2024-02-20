<?php

namespace App\Http\Controllers;

use App\Helpers\UploadFileHelper;
use App\Http\Requests\StoreExcurtionCharacteristicRequest;
use App\Http\Requests\UpdateExcurtionCharacteristicRequest;
use App\Models\Characteristic;
use App\Models\CharacteristicTranslable;
use App\Models\Excurtion;
use App\Models\ExcurtionCharacteristic;
use App\Models\PictureExcurtion;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ExcurtionCharacteristicController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\StoreExcurtionCharacteristicRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreExcurtionCharacteristicRequest $request, $id)
    {
        $message = "Error al crear en la característica.";
        $datos = $this->datos($id);

        $excurtion = Excurtion::findOrFail($id);


        try {
            DB::beginTransaction();

            $excurtion->characteristics2()->detach();

                foreach ($datos['characteristics'] as $characteristic) {
                    Characteristic::addCharacteristic($characteristic, $excurtion->id, null);
                }

                // $this->clearDatabase();
            DB::commit();
        }  catch (Exception $error) {
            DB::rollBack();
            return $error;
        }

        $excurtion = Excurtion::findOrFail($id);

        $data = $excurtion::with(Excurtion::SHOW)->findOrFail($excurtion->id);
        $message = "message_store_200";
        return response(compact("message", "data"));
    }

    public function store_excurtion_characteristics(Request $request, $id)
    {
        $message = "Error al crear en la característica.";
        $datos = $this->datos2($id, $request);

        $excurtion = Excurtion::findOrFail($id);

        try {
            DB::beginTransaction();

            $excurtion->characteristics2()->detach();

                foreach ($datos['characteristics'] as $characteristic) {
                    Characteristic::addCharacteristic2($characteristic, $excurtion->id, null);
                }

                // $this->clearDatabase();
            DB::commit();
        }  catch (Exception $error) {
            DB::rollBack();
            return $error;
        }

        $excurtion = Excurtion::findOrFail($id);

        $data = $excurtion::with(Excurtion::SHOW)->findOrFail($excurtion->id);
        $message = "message_store_200";
        return response(compact("message", "data"));
    }

    public function clearDatabase()
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        $excurtion_characteristics_ids = ExcurtionCharacteristic::get('characteristic_id')->pluck('characteristic_id')->toArray();

        DB::table('characteristics')->where(function($query) use ($excurtion_characteristics_ids) {
                                        $query->whereNotIn('id', $excurtion_characteristics_ids)
                                              ->orWhere(function($query) use($excurtion_characteristics_ids){
                                                  $query->whereNotIn('characteristic_id', $excurtion_characteristics_ids);
                                               });
                                    })->delete();

        // DELETE FROM characteristic_translables WHERE characteristic_id NOT IN (SELECT id FROM characteristics);

        $characteristics_ids = Characteristic::all('id')->pluck('id')->toArray();
        CharacteristicTranslable::whereNotIn('characteristic_id', $characteristics_ids)->delete();
        ExcurtionCharacteristic::whereNull('excurtion_id')->delete();
        // DELETE FROM excurtion_characteristics WHERE excurtion_id IS NULL;
        DB::statement('SET FOREIGN_KEY_CHECKS=1');
    }

    private function deleteCharacteristics($characteristic)
    {
        foreach ($characteristic->characteristics as $charact) {

            if ($charact->characteristics->count() > 0) {
                return
                $this->deleteCharacteristics($charact);
            }

        }

        $characteristic->delete();
    }

    private function datos($excurtion_id)
    {
        switch ($excurtion_id) {
            case 1:
                return $this->minitrekking();
                break;
            case 2:
                return $this->bigIce();
                break;
            case 3:
                return $this->safariNautico();
                break;
            case 4:
                return $this->safariAzul();
                break;
            case 5:
                return $this->minitrekking_2();
                break;
            default:
                return [];
                break;
        }

        return [];
    }

    private function datos2($excurtion_id, $request)
    {
        switch ($excurtion_id) {
            case 1:
                return $this->minitrekking2($request);
                break;
            case 2:
                return $this->bigIce2();
                break;
            case 3:
                return $this->safariNautico2();
                break;
            case 4:
                return $this->safariAzul2();
                break;
            default:
                return [];
                break;
        }

        return [];
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\ExcurtionCharacteristic  $excurtionCharacteristic
     * @return \Illuminate\Http\Response
     */
    public function show(ExcurtionCharacteristic $excurtionCharacteristic)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\ExcurtionCharacteristic  $excurtionCharacteristic
     * @return \Illuminate\Http\Response
     */
    public function edit(ExcurtionCharacteristic $excurtionCharacteristic)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateExcurtionCharacteristicRequest  $request
     * @param  \App\Models\ExcurtionCharacteristic  $excurtionCharacteristic
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateExcurtionCharacteristicRequest $request, ExcurtionCharacteristic $excurtionCharacteristic)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\ExcurtionCharacteristic  $excurtionCharacteristic
     * @return \Illuminate\Http\Response
     */
    public function destroy(ExcurtionCharacteristic $excurtionCharacteristic)
    {
        //
    }


    /**
     * 1- Caracteristica
     * 2- About (sobre está experiencia)
     * 3- Befor bying (Restricciones muy importantes antes de comprar)
     * 5- Itinerario (en vertical en la página)
     * 7- Qué llevar en la exursión
     * 9- Antes de comprar
     * 10 comparison_sail_perito
     * 11- comparison_trekking_ice
     * 12- comparison_dificult
     * 14- comparison_fissures
     * 15- comparison_seracs
     * 16- comparison_sinks
     * 17- comparison_caves
     * 18- comparison_laggons
     * 19- comparison_group_size
     * 20- comparison_lagoon_coast_trekking
     * 21- comparison_forest_trekking
     * 22- comparison_food_included
     * 23- comparison_hotel_transfer
     * 25- comparison_current_price
     */

    public function minitrekking() // convert json to array IMPORTANT
    {
        $characteristics = [];

        //1 characteristics
            $characteristics['characteristics'][] = [
                # Generales"1"
                "icon_id" =>  NULL,
                "icon" =>  NULL,
                "characteristic_type" =>  "characteristics",
                "order" =>  NULL,
                #

                # translables
                    "translables" => [
                        [
                            "lenguage_id" =>  1,
                            "name" =>  "Características de la actividad",
                            "description" =>  NULL
                        ],
                        [
                            "lenguage_id" =>  2,
                            "name" =>  "Activity characteristic",
                            "description" =>  NULL
                        ],
                        [
                            "lenguage_id" =>  3,
                            "name" =>  "Característica da atividade",
                            "description" =>  NULL
                        ]
                    ],
                #

                # Las 6 características o ḿas
                #Translables
                "characteristics" =>
                [
                    #$clock
                        [
                            "icon" =>  '$clock',
                            "order" =>  "1",
                            "translables" =>  [
                                [
                                    #ESPAÑOL
                                    "lenguage_id" =>  "1",
                                    "name"        =>  '<p>9:30 horas (Todo el día)</p>',
                                    "description" =>  '<p>La duración de la actividad es de aproximadamente 9.30hs. Se recomienda no organizar otros planes para ese día.</p>'
                                ],
                                [
                                    # INGLES
                                    "lenguage_id" =>  "2",
                                    "name"        =>  "<p>9:30 hours (Full day)</p>",
                                    "description" =>  "<p>The duration of the activity is approximately 9:30 a.m. It is recommended not to organize other plans for that day.</p>"
                                ],
                                [
                                    # PORTUGUÉS
                                    "lenguage_id" =>  "3",
                                    "name"        =>  "<p>9:30 horas (Todo o dia)</p>",
                                    "description" =>  "<p>A duração da atividade é de aproximadamente 9h30. Recomenda-se não organizar outros planos para esse dia.</p>"
                                ]
                            ]
                        ],
                    #$calendar
                        [
                            "icon" =>  '$calendar',
                            "order" =>  "2",
                            "translables" =>  [
                                [
                                #ESPAÑOL
                                    "lenguage_id" =>  "1",
                                    "name"        =>  "<p>15 Julio al 31 Mayo</p>",
                                    "description" =>  "<p>La disponibilidad de esta excursión es del 15 de Julio al 31 de Mayo</p>"
                                ],
                                [
                                # INGLES
                                    "lenguage_id" =>  "2",
                                    "name"        =>  "<p>July 15th to May 31th</p>",
                                    "description" =>  "<p>The availability of this excursion is from July 15 to May 31</p>"
                                ],
                                [
                                # PORTUGUÉS
                                    "lenguage_id" =>  "3",
                                    "name"        =>  "<p>15 Julho até 31 maio</p>",
                                    "description" =>  "<p>A disponibilidade desta excursão é de 15 de Julho a 31 de Mayo</p>"
                                ]
                            ]
                        ],
                    #$bus
                        [
                            "icon" =>  '$bus',
                            "order" =>  "3",
                            "translables" =>  [
                                [
                                #ESPAÑOL
                                    "lenguage_id" =>  "1",
                                    "name"        =>  '<p>Traslado opcional</p>',
                                    "description" =>  '<p>Opcional traslado con guía y visita de una hora aproximadamente a pasarelas.</p>'
                                ],
                                [
                                # INGLES
                                    "lenguage_id" =>  "2",
                                    "name"        =>  '<p>Optional transfer</p>',
                                    "description" =>  "<p>Optional transfer with guide, including a visit of about one hour to the walkways.</p>"
                                ],
                                [
                                # PORTUGUÉS
                                    "lenguage_id" =>  "3",
                                    "name"        =>  '<p>Transferência opcional</p>',
                                    "description" =>  "<p>Traslado opcional, com guia e visita de aproximadamente uma hora às passarelas.</p>"
                                ]
                            ]
                        ],
                    #$guide
                        [
                            "icon" =>  '$guide',
                            "order" =>  "4",
                            "translables" =>  [
                                [
                                #ESPAÑOL
                                    "lenguage_id" =>  "1",
                                    "name"        =>  "<p>Guías español e inglés.</p>",
                                    "description" =>  "<p>Nuestros guías hablan español e inglés.</p>"
                                ],
                                [
                                # INGLES
                                    "lenguage_id" =>  "2",
                                    "name"        =>  "<p>Spanish and English guides.</p>",
                                    "description" =>  "<p>Our guides speak Spanish and English.</p>"
                                ],
                                [
                                # PORTUGUÉS
                                    "lenguage_id" =>  "3",
                                    "name"        =>  "<p>Guias em espanhol e inglês</p>",
                                    "description" =>  "<p>Nossos guias falam espanhol e inglês.</p>"
                                ]
                            ]
                        ],
                    #$age
                        [
                            "icon" =>  '$age',
                            "order" =>  "5",
                            "translables" =>  [
                                [
                                #ESPAÑOL
                                    "lenguage_id" =>  "1",
                                    "name"        =>  "<p>Apto para 8 a 65 años</p>",
                                    "description" =>  '<p>Solo apto para <span style="color: #366895;">personas de 8 a 65 años.</span> Sin excepción.</p>'
                                ],
                                [
                                # INGLES
                                    "lenguage_id" =>  "2",
                                    "name"        =>  "<p>Suitable for 8 to 65 years old</p>",
                                    "description" =>  '<p>Suitable for <span style="color: #366895;">people between 8 and 65 years ONLY.</span> No exceptions.</p>'
                                ],
                                [
                                # PORTUGUÉS
                                    "lenguage_id" =>  "3",
                                    "name"        =>  "<p>Adequado para 8 a 65 anos</p>",
                                    "description" =>  '<p>Somente apto para <span style="color: #366895;">pessoas entre 8 e 65 anos.</span> Sem exceções.</p>'
                                ]
                            ]
                        ],
                    #$complexity
                        // [
                        //     "icon" =>  '$experience',
                        //     "order" =>  "6",
                        //     "translables" =>  [
                        //         [
                        //         #ESPAÑOL
                        //             "lenguage_id" =>  "1",
                        //             "name"        =>  "<p>Complejidad moderada</p>",
                        //             "description" =>  "<p>Para que tengas una excelente experiencia en el Glaciar debés tener la capacidad psicofísica suficiente para caminar 3 horas, siendo parte del trayecto sobre el hielo y con crampones.</p>"
                        //         ],
                        //         [
                        //         # INGLES
                        //             "lenguage_id" =>  "2",
                        //             "name"        =>  "<p>Moderate complexity</p>",
                        //             "description" =>  "<p>In order to have a great experience on the Glacier, you must have the mental and physical capacity required to walk for 3 hours, partly on ice and with crampons.</p>"
                        //         ],
                        //         [
                        //         # PORTUGUÉS
                        //             "lenguage_id" =>  "3",
                        //             "name"        =>  "<p>Moderada complexidade</p>",
                        //             "description" =>  "<p>Para que você desfrute de uma experiência excelente no Glaciar, é imprescindível contar com capacidade psicofísica suficiente para caminhar 3 horas, uma parte do percorrido sobre gelo e com grampos.</p>"
                        //         ]
                        //     ]
                        // ],
                    #$restrictions_before_buying
                        [
                            "icon" =>  '$walking',
                            "order" =>  "7",
                            "translables" =>  [
                                [
                                #ESPAÑOL
                                    "lenguage_id" =>  "1",
                                    "name"        =>  "<p>Personas sedentarias con obesidad</p>",
                                    "description" =>  "<p>Para tener una excelente experiencia en el Glaciar tanto niños como adultos deben tener la capacidad psicofísica suficiente para caminar 3 horas, de las cuales 1h15´ es sobre el hielo y con crampones.</p>"
                                ],
                                [
                                # INGLES
                                    "lenguage_id" =>  "2",
                                    "name"        =>  "<p>Sedentary people with obesity</p>",
                                    "description" =>  "<p>To have an excellent experience in the Glacier, both children and adults must have sufficient psychophysical capacity to walk 3 hours, of which 1h15' is on the ice and with crampons</p>"
                                ],
                                [
                                # PORTUGUÉS
                                    "lenguage_id" =>  "3",
                                    "name"        =>  "<p>Pessoas sedentárias com obesidade</p>",
                                    "description" =>  "<p>Para ter uma excelente experiência no Glaciar, crianças e adultos devem ter capacidade psicofísica suficiente para caminhar 3 horas, das quais 1h15' é no gelo e com crampons</p>"
                                ]
                            ]
                        ],
                    #$does_not_include
                        [
                            "icon" =>  '$complexity',
                            "order" =>  "8",
                            "translables" =>  [
                                [
                                #ESPAÑOL
                                    "lenguage_id" =>  "1",
                                    "name"        =>  "<p>Items no incluidos</p>",
                                    "description" =>  "<p><strong>No incluye:</strong> Entrada al Parque Nacional | Comida y bebida | Ropa personal adecuada a las condiciones climáticas de la región. (frío, lluvia, viento, nieve)</p>"
                                ],
                                [
                                # INGLES
                                    "lenguage_id" =>  "2",
                                    "name"        =>  "<p>Items not included</p>",
                                    "description" =>  "<p><strong>Not included:</strong> Entrance to the National Park | Food and drink | Personal clothing appropriate to the climatic conditions of the region. (cold, rain, wind, snow)</p>"
                                ],
                                [
                                # PORTUGUÉS
                                    "lenguage_id" =>  "3",
                                    "name"        =>  "<p>Itens não inclusos</p>",
                                    "description" =>  "<p><strong>Não inclui:</strong> Entrada no Parque Nacional | Comida e bebida | Roupa pessoal adequada às condições climáticas da região. (frio, chuva, vento, neve)</p>"
                                ]
                            ]
                        ]
                ]
            ];

        //2 about
            $characteristics['characteristics'][] = [
                    "icon_id" => null,
                    "icon" => null,
                    "characteristic_type" =>  "about",
                    "order" => null,

                    "characteristics" => [],
                    "translables" => [
                        [
                            "lenguage_id" => 1,
                            "name" => "Sobre esta experiencia",
                            "description" => '<p>La excursi&oacute;n comienza con la b&uacute;squeda de los pasajeros en El Calafate. En nuestros confortables buses, camino al Parque Nacional Los Glaciares, los gu&iacute;as de turismo les brindar&aacute;n informaci&oacute;n sobre el lugar, el glaciar y la excursi&oacute;n.</p>
                            <p>&nbsp;</p>
                            <p>Una vez en el puerto &ldquo;Bajo de las Sombras&rdquo; (Ruta 11, a 70 km de El Calafate) se embarca para cruzar el Lago Rico, llegando a la costa opuesta luego de aproximadamente 20 minutos de navegaci&oacute;n frente a la imponente cara sur del Glaciar Perito Moreno.</p>
                            <p>&nbsp;</p>
                            <p>Al desembarcar ser&aacute;n recibidos por nuestros expertos gu&iacute;as de monta&ntilde;a, quienes los conducir&aacute;n a un acogedor refugio con una vista privilegiada del glaciar. A partir de aqu&iacute;, &iexcl;comienza el trekking! Iniciaremos el recorrido con una caminata por la costa del lago y con la vista posada en el glaciar para no perdernos la oportunidad de ver desprendimientos. Luego, los gu&iacute;as brindar&aacute;n una charla sobre glaciolog&iacute;a.</p>
                            <p>&nbsp;</p>
                            <p>Al llegar al borde del glaciar, con las sorprendentes tonalidades azules del hielo enmarcando el paisaje, se organizar&aacute;n subgrupos de un m&aacute;ximo de 20 personas cada uno y se les colocar&aacute;n los crampones y cascos provistos por la empresa. Esta excursi&oacute;n es altamente personalizada (un gu&iacute;a cada 10 pasajeros m&aacute;ximo). Una vez sobre el glaciar, recibir&aacute;n una charla de seguridad y caminar&aacute;n para disfrutar de este para&iacute;so helado declarado Patrimonio de la Humanidad (1981).</p>
                            <p>&nbsp;</p>
                            <p><strong><span style="color: #2471B9;">El circuito sobre el glaciar es de dificultad media, la superficie del hielo es irregular pero firme y segura.</span>&nbsp;</strong>Durante la caminata se podr&aacute; apreciar una variedad de formaciones t&iacute;picas de un glaciar como profundas grietas, sumideros azules, enormes seracs y lagunas turquesas.</p>
                            <p>&nbsp;</p>
                            <p>Al finalizar la caminata sobre el hielo, se recorrer&aacute; la zona periglaciar y la morena lateral desde donde obtendr&aacute;n una vista panor&aacute;mica del Glaciar Perito Moreno, las monta&ntilde;as y del lago. Luego, se emprender&aacute; el regreso por un sendero que atraviesa el exuberante bosque andino patag&oacute;nico, completando as&iacute; las&nbsp;<strong><span style="color: #2471B9;">TRES HORAS DE CAMINATA POR LA COSTA DEL LAGO, MORRENA, HIELO Y BOSQUE (de las cuales, una hora aproximadamente es sobre el hielo glaciar)</span>.</strong>&nbsp;Al llegar al refugio los invitaremos con una bebida caliente y les haremos entrega de un souvenir. Poco tiempo despu&eacute;s embarcar&aacute;n para regresar al Puerto Bajo de las Sombras, pero antes contemplar&aacute;n desde el barco las enormes paredes del glaciar.</p>
                            <p>&nbsp;</p>
                            <p><strong><span style="color: #2471B9;">La duraci&oacute;n de la excursi&oacute;n con el traslado es de alrededor de diez horas en total</span>&nbsp;</strong>e incluye la visita guiada de una hora aproximadamente a las pasarelas del Glaciar Perito Moreno, a 7 km del puerto. All&iacute; podr&aacute;n disfrutar de la espectacular vista panor&aacute;mica del glaciar y recorrer alguno de los senderos autoguiados. En caso de no optar por nuestro transporte e ir por sus propios medios, el Minitrekking dura cuatro horas y media aproximadamente, saliendo desde el Puerto y regresando al mismo punto de partida.</p>
                            <p>&nbsp;</p>
                            <p><span style="color: #2471B9;"><strong>El Minitrekking se realiza en un ambiente natural por lo cual las condiciones climáticas y características del glaciar y sus alrededores cambian diariamente. Sin embargo, la excursión no se suspende, mientras que las condiciones de seguridad lo permitan. ¡Los esperamos!</strong></span></p>
                            <p>&nbsp;</p>
                            <p><span style="color: #2471B9;"><strong>Salidas grupales:</strong></span> Contamos con varias salidas diarias en diferentes horarios. Si viajas en grupo o con un compañero por favor indícanos el detalle con anticipación, para poder ubicarlos en el mismo horario y garantizar la salida grupal en un mismo horario.</p>'
                        ],
                        [
                            "lenguage_id" => 2,
                            "name" => "About",
                            "description" => '<p>The tour starts when passengers are picked up in El Calafate. You will board our comfortable buses, where our tour guides will give you information on the place, the glacier and the tour on our way to Parque Nacional Los Glaciares.</p>
                            <p>&nbsp;</p>
                            <p>Once you arrive at &ldquo;Bajo de las Sombras&rdquo; port (located on Route 11, 70 Km from El Calafate), you&rsquo;ll board a ship to cross Lago Rico and descend on the opposite coast, after a 20-minute navigation in front of the stunning south face of Glaciar Perito Moreno.</p>
                            <p>&nbsp;</p>
                            <p>When you disembark, you will be welcomed by our expert mountain guides, who will lead you to a cozy shelter with a privileged view of the glacier. Here starts the trekking! We will start by walking along the coast of the lake, keeping our eyes on the glacier so that we do not miss any ice calvings. Then, the guides will give a talk about glaciology.</p>
                            <p>&nbsp;</p>
                            <p>When we reach the glacier side, with its amazing shades of blue ice framing the landscape, you will be divided into subgroups of up to 20 people each and you will have the crampons and helmets fitted, which are provided by the company. This tour is highly personalized (one guide every up to 10 passengers). Once on the glacier, you will hear the safety instructions and you will walk to enjoy this frozen paradise, which has been declared a Word Heritage Site in 1981.</p>
                            <p>&nbsp;</p>
                            <p><strong><span style="color: #2471B9;">The level of difficulty of this walk is moderate. The surface of the ice is irregular, but firm and safe</span>.&nbsp;</strong>During the trekking, you will be able to see a variety of typical glacier features, such as deep cracks, blue moulins, huge seracs and turquoise ponds.</p>
                            <p>&nbsp;</p>
                            <p>At the end of the ice trekking, you will walk on the area around the glacier and the side moraine, from where you will be able to enjoy a panoramic view of Glaciar Perito Moreno, the mountains and the lake. Later, you will return along a path crossing the exuberant Bosque Andino Patag&oacute;nico, completing the&nbsp;<span style="color: #2471B9;"><strong>THREE-HOUR&nbsp;WALK ON THE LAKE COAST, MORAINE, ICE AND WOODS (about one hour is on the glacier&rsquo;s ice).</strong></span>&nbsp;When we arrive to the shelter, we will give visitors hot drinks and a souvenir. Shortly after, you will embark to return to &ldquo;Bajo de las Sombras&rdquo; port, but before departing you will be able to watch the huge walls of the glacier from the ship.</p>
                            <p>&nbsp;</p>
                            <p><strong><span style="color: #2471B9;">The duration of this tour is about 10 hours, including the transfe</span>r&nbsp;</strong>and a one-hour guided visit to the walkways of Glaciar Perito Moreno, 7 km from the port. There, you&rsquo;ll enjoy the spectacular panoramic view of the glacier and walk along some of the self-guided paths. If you don&rsquo;t use our transfer and go by your own means, the Minitrekking takes about four hours and a half, leaving from the port and returning to the same point.</p>
                            <p>&nbsp;</p>
                            <p><span style="color: #2471B9;"><strong>The Minitrekking is carried out in a natural environment, so weather conditions and the glacier and its surroundings change every day. However, the excursion is not suspended, as long as security conditions allow it. We are waiting for you!</strong></span></p>
                            <p>&nbsp;</p>
                            <p><strong>Group departures: </strong> we have several departures at different times. If you are traveling in a group or with a partner, please tell us the details in advance, in order to place them at the same time and guarantee the group departure at the same time</p>'
                        ],
                        [
                            "lenguage_id" => 3,
                            "name" => "Sobre esta experiência",
                            "description" => '<p>A excurs&atilde;o come&ccedil;a com a retirada dos passageiros na cidade de El Calafate. Em nossos confort&aacute;veis &ocirc;nibus, caminho ao Parque Nacional Los Glaciares, os guias de turismo oferecer&atilde;o informa&ccedil;&otilde;es sobre o local, a geleira e a excurs&atilde;o.</p>
                            <p>&nbsp;</p>
                            <p>Ao chegar ao porto &ldquo;Bajo de las Sombras&rdquo; (Ruta 11, a 70 km de El Calafate), come&ccedil;a a navega&ccedil;&atilde;o em barco, atravessando o Lago Rico at&eacute; atingir a costa oposta, logo ap&oacute;s 20 minutos de navega&ccedil;&atilde;o com vista para a parede sul do Glaciar Perito Moreno.</p>
                            <p>&nbsp;</p>
                            <p>Ao desembarcar, os passageiros s&atilde;o recebidos por nossos expertos guias de montanha e conduzidos at&eacute; um abrigo aconchegante, com vista privilegiada &agrave; geleira. A partir desse ponto come&ccedil;a o trekking! O percorrido come&ccedil;a com uma caminhada pela costa do lago e sempre com vista &agrave; geleira, para n&atilde;o perdermos a oportunidade de contemplar os desprendimentos. Logo, os guias oferecer&atilde;o informa&ccedil;&otilde;es sobre glaciologia.</p>
                            <p>&nbsp;</p>
                            <p>Ao chegar at&eacute; a borda do glaciar, no meio de uma paisagem de incr&iacute;veis tonalidades azuis, ser&atilde;o organizados subgrupos com at&eacute; 20 pessoas cada um, e ser&atilde;o colocados os grampos e capacetes fornecidos pela empresa. Esta excurs&atilde;o &eacute; muito personalizada, com um guia cada, no m&aacute;ximo, 10 passageiros. J&aacute; na geleira, os passageiros receber&atilde;o instru&ccedil;&otilde;es sobre seguran&ccedil;a e caminhar&atilde;o para desfrutar desse para&iacute;so gelado, que foi declarado Patrim&ocirc;nio da Humanidade em 1981.</p>
                            <p>&nbsp;</p>
                            <p><span style="color: #2471B9;"><strong>O circuito sobre a geleira tem uma dificuldade m&eacute;dia, a superf&iacute;cie do gelo &eacute; irregular, mas firme e segura.</strong></span>&nbsp;No percorrido, os passageiros poder&atilde;o apreciar uma grande variedade de forma&ccedil;&otilde;es caracter&iacute;sticas de uma geleira, como profundas fendas, sumidouros azuis, enormes seracs e lagoas de cor azul turquesa.</p>
                            <p>&nbsp;</p>
                            <p>Ao concluir a caminhada sobre o gelo, os passageiros percorrer&atilde;o a &aacute;rea periglacial e a morena lateral para desfrutar da vista panor&acirc;mica do Glaciar Perito Moreno, das montanhas e do lago. O retorno ser&aacute; por uma trilha que atravessa o exuberante bosque andino patag&ocirc;nico, completando assim as&nbsp;<span style="color: #2471B9;"><strong>TR&Ecirc;S HORAS DE CAMINHADA PELA COSTA DO LAGO, MORENA, GELO E BOSQUE (dessas tr&ecirc;s horas, uma hora aproximadamente ser&aacute; sobre o gelo da geleira).</strong></span>&nbsp;Ao chegar ao abrigo, os passageiros s&atilde;o convidados com bebida quente e receber&atilde;o um souvenir. Pouco tempo depois, embarcar&atilde;o para retornar ao porto Bajo las Sombras, mas antes de partir, contemplar&atilde;o as enormes paredes da geleira.</p>
                            <p>&nbsp;</p>
                            <p><span style="color: #2471B9;"><strong>A dura&ccedil;&atilde;o total da excurs&atilde;o mais o traslado &eacute; de aproximadamente dez horas</strong></span>&nbsp;e inclui uma visita guiada de perto de uma hora &agrave;s passarelas do Glaciar Perito Moreno, a 7 km do porto. L&aacute; desfrutar&atilde;o da espetacular vista panor&acirc;mica do glaciar e percorrer&atilde;o algumas das trilhas autoguiadas. Se voc&ecirc; n&atilde;o escolher nosso transporte e utilizar seus pr&oacute;prios meios, lembre-se que a dura&ccedil;&atilde;o do Minitrekking &eacute; quatro horas e meia aproximadamente, saindo do porto e voltando para o mesmo ponto de sa&iacute;da.</p>
                            <p>&nbsp;</p>
                            <p><span style="color: #2471B9;"><strong>O Minitrekking &eacute; realizado em um ambiente natural e com condi&ccedil;&otilde;es clim&aacute;ticas e caracter&iacute;sticas da geleira e seu entorno que mudam todos os dias. No entanto, a excurs&atilde;o n&atilde;o est&aacute; suspensa, desde que as condi&ccedil;&otilde;es de seguran&ccedil;a o permitan. Esperamos voc&ecirc;s!</strong></span></p>
                            <p>&nbsp;</p>
                            <p><strong>Saídas em grupo: </strong> temos várias saídas em horários diferentes. Se viaja em group ou a dois, por favor indique-nos os detalhes com antecedencia, de forma a colocá-los a mesma hora a garantir a saída di grupo a mesma hora</p>'
                        ]
                    ]
            ];

        //3 before_buying
            $characteristics['characteristics'][] = [
                "icon_id" => null,
                "characteristic_type" => "before_buying",
                "order" => null,

                "characteristics" => [
                    [
                        "icon" => null,
                        "order" => null,
                        "characteristics" => [
                            [
                                "icon_id" => null,
                                "icon" => '$obesity',
                                "order" => null,

                                "characteristics" => [],
                                "translables" => [
                                    [
                                        "lenguage_id" => 1,
                                        "name" => null,
                                        "description" => "<li><strong>Personas sedentarias con obesidad.</strong> No podrán participar personas con obesidad. En el caso de los adultos, la Organización Mundial de la Salud (OMS) define que la obesidad es tal, cuando una persona presenta un Índice de Masa Corporal (IMC) igual o superior a 30. El IMC se calcula dividiendo el peso de una persona en kilos por el cuadrado de su talla en metros: (kg/m2). Ante cualquier duda o consulta, envíanos una mail a clientes@hieloyaventura.com</li>",
                                    ],
                                    [
                                        "lenguage_id" => 2,
                                        "name" => null,
                                        "description" => '<li><strong>Obese persons.</strong> People with obesity may not participate. In the case of adults, the World Health Organization (WHO) defines obesity as when a person has a Body Mass Index (BMI) equal to or greater than 30. The BMI is calculated by dividing the weight of a person in kilos times the square of their height in meters: (kg/m2). If you have any questions or queries, send us an email to clientes@hieloyaventura.com</li>',
                                    ],
                                    [
                                        "lenguage_id" => 3,
                                        "name" => null,
                                        "description" => '<li><strong>Pessoas obesas.</strong> Pessoas com obesidade não podem participar. No caso dos adultos, a Organização Mundial de Saúde (OMS) define a obesidade como quando uma pessoa tem um Índice de Massa Corporal (IMC) igual ou superior a 30. O IMC é calculado dividindo o peso de uma pessoa em quilos vezes o quadrado de sua altura em metros: (kg/m2). Se tiver alguma dúvida ou questão, envie-nos um email para clientes@hieloyaventura.com</li>'
                                    ]
                                ]
                            ],
                            [
                                "icon_id" => null,
                                "order" => null,
                                "icon" => '$pregnant',

                                "characteristics" => [],
                                "translables" => [
                                    [
                                        "lenguage_id" => 1,
                                        "name" => null,
                                        "description" => "<li>Embarazadas</li>",
                                    ],
                                    [
                                        "lenguage_id" => 2,
                                        "name" => null,
                                        "description" => "<li>Pregnant</li>",
                                    ],
                                    [
                                        "lenguage_id" => 3,
                                        "name" => null,
                                        "description" => "<li>Grávida</li>",
                                    ]
                                ]
                            ],
                            [
                                "icon_id" => null,
                                "order" => null,
                                "icon" => '$wheel_chair',

                                "characteristics" => [],
                                "translables" => [
                                    [
                                        "lenguage_id" => 1,
                                        "name" => null,
                                        "description" => "<li>Personas con cualquier grado o tipo de discapacidad física o mental que afecte su atención, marcha y/o coordinación.</li>",
                                    ],
                                    [
                                        "lenguage_id" => 2,
                                        "name" => null,
                                        "description" => "<li>People with any degree of physical or mental disability that affects their attention, ability to walk and/or coordination.</li>",
                                    ],
                                    [
                                        "lenguage_id" => 3,
                                        "name" => null,
                                        "description" => "<li>Pessoas com qualquer grau ou tipo de deficiência física ou mental que possa afetar sua atenção, marcha e/ou coordenação.</li>",
                                    ]
                                ]
                            ],
                            [
                                "icon_id" => null,
                                "order" => null,
                                "icon" =>  '$age_yellow',
                                "translables" =>  [
                                    [
                                    #ESPAÑOL
                                        "lenguage_id" =>  "1",
                                        "name"        =>  "<p>Apto para 8 a 65 años</p>",
                                        "description" =>  '<p>Solo apto para <span style="color: #366895;">personas de 8 a 65 años.</span> Sin excepción.</p>'
                                    ],
                                    [
                                    # INGLES
                                        "lenguage_id" =>  "2",
                                        "name"        =>  "<p>Suitable for 8 to 65 years old</p>",
                                        "description" =>  '<p>Suitable for <span style="color: #366895;">people between 8 and 65 years ONLY.</span> No exceptions.</p>'
                                    ],
                                    [
                                    # PORTUGUÉS
                                        "lenguage_id" =>  "3",
                                        "name"        =>  "<p>Adequado para 8 a 65 anos</p>",
                                        "description" =>  '<p>Somente apto para <span style="color: #366895;">pessoas entre 8 e 65 anos.</span> Sem exceções.</p>'
                                    ]
                                ]
                            ],
                            [
                                "icon_id" => null,
                                "order" => null,
                                "icon" => '$heart_rate',

                                "characteristics" => [],
                                "translables" => [
                                    [
                                        "lenguage_id" => 1,
                                        "name" => null,
                                        "description" => "<li>Personas con antecedentes cardíacos. Personas que sufran enfermedades cardiovasculares centrales o periféricas, que sus capacidades cardíacas o vasculares se encuentren disminuidas, o utilicen stent, bypass, marcapasos u otras prótesis. Ejemplo: medicamentos anticoagulantes, varices grado III (las que se evidencian gruesas y múltiples).</li>",
                                    ],
                                    [
                                        "lenguage_id" => 2,
                                        "name" => null,
                                        "description" => "<li>People with a cardiac history. Persons who suffer from central or peripheral heart or vascular disease, whose heart or capabilities are limited, or people with stents, bypass, pacemakers or other prosthesis. Example: anticoagulant medication, stage 3 varicose veins (multiple thick varicose veins that can noticed).</li>",
                                    ],
                                    [
                                        "lenguage_id" => 3,
                                        "name" => null,
                                        "description" => "<li>Pessoas com histórico de doença cardíaca. Pessoas com doenças cardiovasculares centrais ou periféricas, com capacidades cardíacas ou vasculares deficientes, ou quando utilizem stent, bypass, marca-passos ou outro tipo de prótese. Exemplo: medicamentos anti-coagulantes, varizes grau III (são grossas e múltiplas).Pessoas com doenças cardiovasculares centrais ou periféricas, com capacidades cardíacas ou vasculares deficientes, ou quando utilizem stent, bypass, marca-passos ou outro tipo de prótese. Exemplo: medicamentos anti-coagulantes, varizes grau III (são grossas e múltiplas).</li>",
                                    ]
                                ]
                            ],
                            [
                                "icon_id" => null,
                                "order" => null,
                                "icon" => '$lung',

                                "characteristics" => [],
                                "translables" => [
                                    [
                                        "lenguage_id" => 1,
                                        "name" => null,
                                        "description" => "<li>Personas que padezcan enfermedades provocadas POR discapacidades respiratorias como EPOC, asma, enfisema, entre otras.</li>",
                                    ],
                                    [
                                        "lenguage_id" => 2,
                                        "name" => null,
                                        "description" => "<li>People who suffer from diseases caused by respiratory disabilities such as COPD, asthma, emphysema, among others.</li>",
                                    ],
                                    [
                                        "lenguage_id" => 3,
                                        "name" => null,
                                        "description" => "<li>Pessoas que sofrem de doenças causadas por deficiências respiratórias como DPOC, asma, enfisema, entre outras.</li>",
                                    ]
                                ]
                            ]
                        ],
                        "translables" => []
                    ],
                    // [ //esta característica no está en BIG ICE, pero si tiene otras. Tengo que preguntar por eso
                    //     "icon_id" => null,
                    //     "order" => null,
                    //     "icon" => null,

                    //     "characteristics" => [],
                    //     "translables" => [
                    //         [
                    //             "lenguage_id" => 1,
                    //             "name" => null,
                    //             "description" => "<p>Los niños deben tener la capacidad psicofísica suficiente de para caminar 3 horas, de las cuales 1 hora y media es sobre el hielo con crampones.</p>"
                    //         ]
                    //     ]
                    // ]
                ],
                "translables" => [
                    [
                        "lenguage_id" => 1,
                        "name" => "A TENER EN CUENTA ANTES DE COMPRAR",
                        "description" => "<p><strong>Debido al grado de esfuerzo y dificultad (MODERADA, con pronunciadas subidas y bajadas en un terreno irregular) que esta actividad presenta y con el solo objetivo de preservar la salud, no podrán participar:</strong></p>"
                    ],
                    [
                        "lenguage_id" => 2,
                        "name" => "BEFORE PURCHASING YOUR TICKETS, PLEASE KEEP IN MIND THE FOLLOWING:",
                        "description" => "<p><strong>Due to the effort and difficulty levels (MODERATE, with steep and uneven ascents and descents) of this activity, and in order to preserve their health, the following persons cannot take the tour:</strong></p>"
                    ],
                    [
                        "lenguage_id" => 3,
                        "name" => "LEVAR EM CONTA ANTES DE COMPRAR",
                        "description" => "<p><strong>Devido ao nível de esforço e dificuldade da atividade (MODERADA, com subidas e descidas pronunciadas e irregulares), e visando a proteger sua saúde, as pessoas a seguir não podem participar da excursão:</strong></p>"
                    ]
                ]
            ];

        //5 itinerary
            $characteristics['characteristics'][] = [
                "icon_id" => null,
                "characteristic_type" => 'itinerary',
                "order" => null,

                "characteristics" => [
                    [
                        "icon_id" => null,
                        "order" => null,
                        "icon" => null,
                        "characteristics" => [
                            [
                                "icon_id" => null,
                                "order" => null,
                                "icon" => '$itinerary_point',
                                "characteristics" => [],
                                "translables" => [
                                    [
                                        "lenguage_id" => 1,
                                        "name" => "Salida de El Calafate",
                                        "description" => "70km al glaciar."
                                    ],
                                    [
                                        "lenguage_id" => 2,
                                        "name" => "Departure from El Calafate",
                                        "description" => "70km to the glacier"
                                    ],
                                    [
                                        "lenguage_id" => 3,
                                        "name" => "Partida de El Calafate",
                                        "description" => "70 km até a geleira."
                                    ]
                                ]
                            ],
                            [
                                "icon_id" => null,
                                "order" => null,
                                "icon" => '$itinerary_ship',
                                "characteristics" => [],
                                "translables" => [
                                    [
                                        "lenguage_id" => 1,
                                        "name" => "Embarque en Puerto",
                                        "description" => "20min de navegación."
                                    ],
                                    [
                                        "lenguage_id" => 2,
                                        "name" => "Boarding in port",
                                        "description" => "20min of navigation."
                                    ],
                                    [
                                        "lenguage_id" => 3,
                                        "name" => "Embarque no porto",
                                        "description" => "20min de navegação."
                                    ]
                                ]
                            ],
                            [
                                "icon_id" => null,
                                "order" => null,
                                "icon" => '$itinerary_shoe',
                                "characteristics" => [],
                                "translables" => [
                                    [
                                        "lenguage_id" => 1,
                                        "name" => "Trekking por la costa, el glaciar y el bosque",
                                        "description" => "3 horas."
                                    ],
                                    [
                                        "lenguage_id" => 2,
                                        "name" => "trekking along the coast, the glacier and the forest",
                                        "description" => "3 hours."
                                    ],
                                    [
                                        "lenguage_id" => 3,
                                        "name" => "Trekking ao longo da costa, a geleira e a floresta",
                                        "description" => "3 horas."
                                    ]
                                ]
                            ],
                            [
                                "icon_id" => null,
                                "order" => null,
                                "icon" => '$itinerary_ship',
                                "characteristics" => [],
                                "translables" => [
                                    [
                                        "lenguage_id" => 1,
                                        "name" => "Regreso al Puerto",
                                        "description" => "30 minurtos de navegación."
                                    ],
                                    [
                                        "lenguage_id" => 2,
                                        "name" => "Return to port",
                                        "description" => "30min of navigation."
                                    ],
                                    [
                                        "lenguage_id" => 3,
                                        "name" => "Retornar ao porto",
                                        "description" => "30min de navegação."
                                    ]
                                ]
                            ],
                            [
                                "icon_id" => null,
                                "order" => null,
                                "icon" => '$blue_stairs',
                                "characteristics" => [],
                                "translables" => [
                                    [
                                        "lenguage_id" => 1,
                                        "name" => "Visita a Pasarelas",
                                        "description" => "1hs aproximadamente"
                                    ],
                                    [
                                        "lenguage_id" => 2,
                                        "name" => "Visit to walkways",
                                        "description" => "1 hour approximately"
                                    ],
                                    [
                                        "lenguage_id" => 3,
                                        "name" => "Visita a passarelas",
                                        "description" => "1 hora aproximadamente"
                                    ]
                                ]
                            ],
                            [
                                "icon_id" => null,
                                "order" => null,
                                "icon" => '$itinerary_point',
                                "characteristics" => [],
                                "translables" => [
                                    [
                                        "lenguage_id" => 1,
                                        "name" => "Regreso a El Calafate",
                                        "description" => "70km"
                                    ],
                                    [
                                        "lenguage_id" => 2,
                                        "name" => "Return to El Calafate",
                                        "description" => "70km"
                                    ],
                                    [
                                        "lenguage_id" => 3,
                                        "name" => "Voltar para El Calafate",
                                        "description" => "70km"
                                    ]
                                ]
                            ]
                        ],
                        "translables" => []
                    ],
                    [
                        "icon_id" => null,
                        "order" => null,
                        "icon" => null,
                        "characteristics" => [
                            [
                                "icon_id" => null,
                                "order" => null,
                                "icon" => null,
                                "characteristics" => [],
                                "translables" => [
                                    [
                                        "lenguage_id" => 1,
                                        "name" => "OPCIONAL CON TRASLADO",
                                        "description" => null
                                    ],
                                    [
                                        "lenguage_id" => 2,
                                        "name" => "OPTIONAL WITH TRANSFER",
                                        "description" => null
                                    ],
                                    [
                                        "lenguage_id" => 3,
                                        "name" => "OPCIONAL COM TRANSFER",
                                        "description" => null
                                    ]
                                ]
                            ],
                            [
                                "icon_id" => null,
                                "order" => null,
                                "icon" => null,
                                "characteristics" => [],
                                "translables" => [
                                    [
                                        "lenguage_id" => 1,
                                        "name" => "TOUR INCLUIDO",
                                        "description" => null
                                    ],
                                    [
                                        "lenguage_id" => 2,
                                        "name" => "TOUR INCLUDED",
                                        "description" => null
                                    ],
                                    [
                                        "lenguage_id" => 3,
                                        "name" => "PASSEIO INCLUÍDO",
                                        "description" => null
                                    ]
                                ]
                            ]
                        ],
                        "translables" => []
                    ]
                ],
                "translables" => [
                    [
                        "lenguage_id" => 1,
                        "name" => "Itinerario Minitrekking",
                        "description" => null
                    ],
                    [
                        "lenguage_id" => 2,
                        "name" => "Minitrekking itinerary",
                        "description" => null
                    ],
                    [
                        "lenguage_id" => 3,
                        "name" => "Itinerário Minitrekking",
                        "description" => null
                    ]
                ]
            ];

        //7 carry
            $characteristics['characteristics'][] =
                [
                    "icon_id" => null,
                    "characteristic_type" => "carry",
                    "order" => null,
                    "icon" => null,
                    "characteristics" => [
                        [
                            "icon_id" => null,
                            "order" => null,
                            "icon" => '$cloth',
                            "characteristics" => [],
                            "translables" => [
                                [
                                    "lenguage_id" => 1,
                                    "name" => null,
                                    "description" => "<p>Vestir ropa c&oacute;moda y abrigada. Campera&nbsp;y&nbsp;pantal&oacute;n impermeable,&nbsp;calzado deportivo o&nbsp;botas de&nbsp;trekking&nbsp;impermeables.&nbsp;El clima es cambiante y hay que estar preparado para no mojarse ni pasar fr&iacute;o.&nbsp;Lentes de sol, protector solar, guantes, gorro.</p>"
                                ],
                                [
                                    "lenguage_id" => 2,
                                    "name" => null,
                                    "description" => "<p>Wear comfortable and warm clothes. A jacket, sports shoes or trekking boots, sunglasses, sunscreen, gloves, a wool hat.</p>"
                                ],
                                [
                                    "lenguage_id" => 3,
                                    "name" => null,
                                    "description" => "<p>Roupa confort&aacute;vel e quente. Casaco, cal&ccedil;ado esportivo ou botas de trekking, &oacute;culos de sol, protetor solar, luvas e gorro.</p>"
                                ]
                            ]
                        ],
                        [
                            "icon_id" => null,
                            "order" => null,
                            "icon" => '$food',
                            "characteristics" => [],
                            "translables" => [
                                [
                                    "lenguage_id" => 1,
                                    "name" => null,
                                    "description" => "<p>Llevar comida y bebida para el d&iacute;a. La Empresa no cuenta con servicio de venta de comidas ni bebidas.</p>"
                                ],
                                [
                                    "lenguage_id" => 2,
                                    "name" => null,
                                    "description" => "<p>Bring food and drink for the day. The company does not sell food and drinks</p>"
                                ],
                                [
                                    "lenguage_id" => 3,
                                    "name" => null,
                                    "description" => "<p>Levar comida e bebida para todo o dia. A empresa n&atilde;o oferece servi&ccedil;o de venda de comidas nem bebidas.</p>"
                                ]
                            ]
                        ],
                        [
                            "icon_id" => null,
                            "characteristic_type" => null,
                            "order" => null,
                            "icon" => '$ticket',
                            "characteristics" => [],
                            "translables" => [
                                [
                                    "lenguage_id" => 1,
                                    "name" => null,
                                    "description" => '<p>Deber&aacute;s presentar tu entrada al Parque Nacional. Pod&eacute;s comprarla&nbsp;<a href="https://ventaweb.apn.gob.ar/reserva/inicio?dp=05" target="_blank" rel="noopener"><strong>a<span style="color: #2471B9;">c&aacute;</span><span style="color: #2471B9;"> (Seleccionar: &ldquo;Acceso Corredor Rio Mitre y Glaciar Perito Moreno&rdquo;)</span></strong></a>&nbsp;o abonarla en efectivo (en pesos argentinos) al llegar al Parque Nacional.</p>'
                                ],
                                [
                                    "lenguage_id" => 2,
                                    "name" => null,
                                    "description" => '<p>Tickets must be exhibited at the entrance of the Parque Nacional. You can buy your ticket here&nbsp;<a href="https://ventaweb.apn.gob.ar/reserva/inicio?dp=05" target="_blank" rel="noopener"><strong>(<span style="color: #2471B9;">Select: &ldquo;Acceso Corredor Rio Mitre y Glaciar Perito Moreno&rdquo;)</span></strong></a>&nbsp;or pay it in cash (in Argentine pesos) when you arrive at the Parque Nacional.</p>'
                                ],
                                [
                                    "lenguage_id" => 3,
                                    "name" => null,
                                    "description" => '<p>Voc&ecirc; dever&aacute; apresentar seu ingresso ao Parque Nacional. Pode comprar o ingresso aqui&nbsp;<span style="color: #2471B9;"><a style="color: #2471B9;" href="https://ventaweb.apn.gob.ar/reserva/inicio?dp=05" target="_blank" rel="noopener"><strong>(Selecionar: &ldquo;Acceso Corredor Rio Mitre y Glaciar Perito Moreno&rdquo;)</strong></a></span>&nbsp;ou pagar com dinheiro (pesos argentinos) ao chegar ao Parque Nacional.</p>'
                                ]
                            ]
                        ]
                    ],
                    "translables" => [
                        [
                            "lenguage_id" => 1,
                            "name" => "Qué llevar en la excursión?",
                            "description" => null
                        ],
                        [
                            "lenguage_id" => 2,
                            "name" => "What SHOULD I bring?",
                            "description" => null
                        ],
                        [
                            "lenguage_id" => 3,
                            "name" => "O que É PRECISO levar?",
                            "description" => null
                        ]
                    ]
                ];
        //9 restrictions
            $characteristics['characteristics'][] = [
                "icon_id" => null,
                "characteristic_type" => "restrictions",
                "order" => null,
                "icon" => null,

                "characteristics" => [],
                "translables" => [
                    [
                        "lenguage_id" => 1,
                        "name" => "Restricciones importantes antes de comprar",
                        "description" => '<p>Debido al grado de esfuerzo y dificultad que esta actividad presenta y con el solo objetivo de preservar la salud, no podrán participar de la excursión ciertas personas.</p>
                        <p style="text-align: justify;">&nbsp;</p>'
                    ],
                    [
                        "lenguage_id" => 2,
                        "name" => "Important restrictions before buying",
                        "description" => '<p>Due to the degree of effort and difficulty that this activity presents and with the sole objective of preserving health, people with:</p>
                        <p style="text-align: justify;">&nbsp;</p>'
                    ],
                    [
                        "lenguage_id" => 3,
                        "name" => "Restrições importantes antes de comprar",
                        "description" => '<p>Devido ao grau de esforço e dificuldade que esta atividade apresenta e com o único objetivo de preservar a saúde, as pessoas com:</p>
                        <p style="text-align: justify;">&nbsp;</p>'
                    ]
                ]
            ];

        //10 comparison_sail_perito
            $characteristics['characteristics'][] = [
                "icon_id" => null,
                "characteristic_type" => "comparison_sail_perito",
                "order" => null,
                "icon" => null,

                "characteristics" => [],
                "translables" => [
                    [
                        "lenguage_id" => "1",
                        "name" => "Navega frente al Glaciar Perito Moreno",
                        "description" => "1"
                    ],
                    [
                        "lenguage_id" => "2",
                        "name" => "Sail in front of the Perito Moreno Glacier",
                        "description" => "1"
                    ],
                    [
                        "lenguage_id" => "3",
                        "name" => "Navegue em frente ao Glaciar Perito Moreno",
                        "description" => "1"
                    ]
                ]
            ];
        //11 comparison_trekking_ice
            $characteristics['characteristics'][] = [
                "icon_id" => null,
                "characteristic_type" => "comparison_trekking_ice",
                "order" => null,
                "icon" => null,

                "characteristics" => [],
                "translables" => [
                    [
                        "lenguage_id" => "1",
                        "name" => "Trekking sobre hielo",
                        "description" => "1hr 15'"
                    ],
                    [
                        "lenguage_id" => "2",
                        "name" => "Ice trekking",
                        "description" => "1hr 15'"
                    ],
                    [
                        "lenguage_id" => "3",
                        "name" => "Trekking no gelo",
                        "description" => "1hr 15'"
                    ]
                ]
            ];
        //12 comparison_dificult
            $characteristics['characteristics'][] = [
                "icon_id" => null,
                "characteristic_type" => "comparison_dificult",
                "order" => null,
                "icon" => null,

                "characteristics" => [],
                "translables" => [
                    [
                        "lenguage_id" => "1",
                        "name" => "Dificultad",
                        "description" => "Moderada"
                    ],
                    [
                        "lenguage_id" => "2",
                        "name" => "Difficulty",
                        "description" => "Moderate"
                    ],
                    [
                        "lenguage_id" => "3",
                        "name" => "Dificuldade",
                        "description" => "Moderada"
                    ]
                ]
            ];
        //14 comparison_fissures
            $characteristics['characteristics'][] = [
                "icon_id" => null,
                "characteristic_type" => "comparison_fissures",
                "order" => null,
                "icon" => null,

                "characteristics" => [],
                "translables" => [
                    [
                        "lenguage_id" => "1",
                        "name" => "Vista de grietas",
                        "description" => "1"
                    ],
                    [
                        "lenguage_id" => "2",
                        "name" => "View of cracks",
                        "description" => "1"
                    ],
                    [
                        "lenguage_id" => "3",
                        "name" => "Visão de rachaduras",
                        "description" => "1"
                    ]
                ]
            ];
        //15 comparison_seracs
            $characteristics['characteristics'][] = [
                "icon_id" => null,
                "characteristic_type" => "comparison_seracs",
                "order" => null,
                "icon" => null,

                "characteristics" => [],
                "translables" => [
                    [
                        "lenguage_id" => "1",
                        "name" => "Vista de Seracs",
                        "description" => "1"
                    ],
                    [
                        "lenguage_id" => "2",
                        "name" => "View of Seracs",
                        "description" => "1"
                    ],
                    [
                        "lenguage_id" => "3",
                        "name" => "Visão de Seracs",
                        "description" => "1"
                    ]
                ]
            ];
        //16 comparison_sinks
            $characteristics['characteristics'][] = [
                "icon_id" => null,
                "characteristic_type" => "comparison_sinks",
                "order" => null,
                "icon" => null,

                "characteristics" => [],
                "translables" => [
                    [
                        "lenguage_id" => "1",
                        "name" => "Vista de sumideros",
                        "description" => "1"
                    ],
                    [
                        "lenguage_id" => "2",
                        "name" => "View of sinkholes",
                        "description" => "1"
                    ],
                    [
                        "lenguage_id" => "3",
                        "name" => "Visão dos sumidouros",
                        "description" => "1"
                    ]
                ]
            ];
        //17 comparison_caves
            $characteristics['characteristics'][] = [
                "icon_id" => null,
                "characteristic_type" => "comparison_caves",
                "order" => null,
                "icon" => null,

                "characteristics" => [],
                "translables" => [
                    [
                        "lenguage_id" => "1",
                        "name" => "Vista de cuevas",
                        "description" => "eventualmente"
                    ],
                    [
                        "lenguage_id" => "2",
                        "name" => "View of caves",
                        "description" => "eventually"
                    ],
                    [
                        "lenguage_id" => "3",
                        "name" => "Vista das cavernas",
                        "description" => "eventualmente"
                    ]
                ]
            ];
        //18 comparison_laggons
            $characteristics['characteristics'][] = [
                "icon_id" => null,
                "characteristic_type" => "comparison_laggons",
                "order" => null,
                "icon" => null,
                "characteristics" => [],
                "translables" => [
                    [
                        "lenguage_id" => "1",
                        "name" => "Vista de lagunas",
                        "description" => "eventualmente"
                    ],
                    [
                        "lenguage_id" => "2",
                        "name" => "View of lagoons",
                        "description" => "eventually"
                    ],
                    [
                        "lenguage_id" => "3",
                        "name" => "Vista das lagoas",
                        "description" => "eventualmente"
                    ]
                ]
            ];
        //19 comparison_group_size
            $characteristics['characteristics'][] = [
                "icon_id" => null,
                "characteristic_type" => "comparison_group_size",
                "order" => null,
                "icon" => null,
                "characteristics" => [],
                "translables" => [
                    [
                        "lenguage_id" => "1",
                        "name" => "Tamaño de grupo",
                        "description" => "20 sobre el hielo"
                    ],
                    [
                        "lenguage_id" => "2",
                        "name" => "Group size",
                        "description" => "20 on ice"
                    ],
                    [
                        "lenguage_id" => "3",
                        "name" => "Tamanho do grupo",
                        "description" => "20 no gelo"
                    ]
                ]
            ];
        //20 comparison_lagoon_coast_trekking
            $characteristics['characteristics'][] = [
                "icon_id" => null,
                "characteristic_type" => "comparison_lagoon_coast_trekking",
                "order" => null,
                "icon" => null,
                "characteristics" => [],
                "translables" => [
                    [
                        "lenguage_id" => "1",
                        "name" => "Trekking por costa del lago",
                        "description" => "1"
                    ],
                    [
                        "lenguage_id" => "2",
                        "name" => "Trekking along the lake coast",
                        "description" => "1"
                    ],
                    [
                        "lenguage_id" => "3",
                        "name" => "Trekking ao longo da costa do lago",
                        "description" => "1"
                    ]
                ]
            ];
        //21 comparison_forest_trekking
            $characteristics['characteristics'][] = [
                "icon_id" => null,
                "characteristic_type" => "comparison_forest_trekking",
                "order" => null,
                "icon" => null,
                "characteristics" => [],
                "translables" => [
                    [
                        "lenguage_id" => "1",
                        "name" => "Trekking por bosque",
                        "description" => "1"
                    ],
                    [
                        "lenguage_id" => "2",
                        "name" => "Trekking through forest",
                        "description" => "1"
                    ],
                    [
                        "lenguage_id" => "3",
                        "name" => "Trekking pela floresta",
                        "description" => "1"
                    ]
                ]
            ];
        //22 comparison_food_included
            $characteristics['characteristics'][] = [
                "icon_id" => null,
                "characteristic_type" => "comparison_food_included",
                "order" => null,
                "icon" => null,
                "characteristics" => [],
                "translables" => [
                    [
                        "lenguage_id" => "1",
                        "name" => "Comida incluida",
                        "description" => "0"
                    ],
                    [
                        "lenguage_id" => "2",
                        "name" => "Lunch included",
                        "description" => "0"
                    ],
                    [
                        "lenguage_id" => "3",
                        "name" => "Almoço incluso",
                        "description" => "0"
                    ]
                ]
            ];
        //23 comparison_hotel_transfer
            $characteristics['characteristics'][] = [
                "icon_id" => null,
                "characteristic_type" => "comparison_hotel_transfer",
                "order" => null,
                "icon" => null,
                "characteristics" => [],
                "translables" => [
                    [
                        "lenguage_id" => "1",
                        "name" => "Traslado desde el hotel",
                        "description" => "optativo"
                    ],
                    [
                        "lenguage_id" => "2",
                        "name" => "Transfer from the hotel",
                        "description" => "optional"
                    ],
                    [
                        "lenguage_id" => "3",
                        "name" => "Traslado do hotel",
                        "description" => "opcional"
                    ]
                ]
            ];
        //25 comparison_current_price
            $characteristics['characteristics'][] = [
                "icon_id" => null,
                "characteristic_type" => "comparison_current_price",
                "order" => null,
                "icon" => null,
                "characteristics" => [],
                "translables" => [
                    [
                        "lenguage_id" => "1",
                        "name" => "Precio actual",
                        "description" => 42000
                    ],
                    [
                        "lenguage_id" => "2",
                        "name" => "Actual Price",
                        "description" => 42000
                    ],
                    [
                        "lenguage_id" => "3",
                        "name" => "Preço real",
                        "description" => 42000
                    ]
                ]
            ];

        //26 purchase_detail
            $characteristics['characteristics'][] = [
                "icon_id" => null,
                "characteristic_type" => 'purchase_detail',
                "order" => null,

                "characteristics" => [
                    [
                        "icon_id" => null,
                        "order" => null,
                        "icon" => null,
                        "characteristics" => [
                            [
                                "icon_id" => null,
                                "order" => null,
                                "icon" => '$check',
                                "con_trf" => null,
                                "characteristics" => [],
                                "translables" => [
                                    [
                                        "lenguage_id" => 1,
                                        "name" => "Guía español e inglés durante el recorrido en el glaciar",
                                        "description" => ""
                                    ],
                                    [
                                        "lenguage_id" => 2,
                                        "name" => "Spanish and English guide during the tour on the glacier",
                                        "description" => ""
                                    ],
                                    [
                                        "lenguage_id" => 3,
                                        "name" => "Guia espanhol e inglês durante o passeio pela geleira",
                                        "description" => ""
                                    ]
                                ]
                            ],
                            // [
                            //     "icon_id" => null,
                            //     "order" => null,
                            //     "icon" => '$check',
                            //     "con_trf" => 1,
                            //     "characteristics" => [],
                            //     "translables" => [
                            //         [
                            //             "lenguage_id" => 1,
                            //             "name" => "Visita de 1 hora a las pasarelas",
                            //             "description" => null
                            //         ],
                            //         [
                            //             "lenguage_id" => 2,
                            //             "name" => "1 hour visit to the catwalks",
                            //             "description" => null
                            //         ],
                            //         [
                            //             "lenguage_id" => 3,
                            //             "name" => "1 hora de visita às passarelas",
                            //             "description" => null
                            //         ]
                            //     ]
                            // ],
                            [
                                "icon_id" => null,
                                "order" => null,
                                "icon" => '$check',
                                "con_trf" => null,
                                "characteristics" => [],
                                "translables" => [
                                    [
                                        "lenguage_id" => 1,
                                        "name" => "Navegación frente a la pared del glaciar",
                                        "description" => null
                                    ],
                                    [
                                        "lenguage_id" => 2,
                                        "name" => "Navigation in front of the glacier wall",
                                        "description" => null
                                    ],
                                    [
                                        "lenguage_id" => 3,
                                        "name" => "Navegação em frente à parede da geleira",
                                        "description" => null
                                    ]
                                ]
                            ],
                            [
                                "icon_id" => null,
                                "order" => null,
                                "icon" => '$check',
                                "con_trf" => null,
                                "characteristics" => [],
                                "translables" => [
                                    [
                                        "lenguage_id" => 1,
                                        "name" => "Trekking sobre el hielo de 1 hora",
                                        "description" => null
                                    ],
                                    [
                                        "lenguage_id" => 2,
                                        "name" => "1 hour ice trekking",
                                        "description" => null
                                    ],
                                    [
                                        "lenguage_id" => 3,
                                        "name" => "1 hora de caminhada no gelo",
                                        "description" => null
                                    ]
                                ]
                            ],
                            [
                                "icon_id" => null,
                                "order" => null,
                                "icon" => '$check',
                                "con_trf" => null,
                                "characteristics" => [],
                                "translables" => [
                                    [
                                        "lenguage_id" => 1,
                                        "name" => "Acceso a nuestro refugio de montaña",
                                        "description" => null
                                    ],
                                    [
                                        "lenguage_id" => 2,
                                        "name" => "Access to our mountain refuge",
                                        "description" => null
                                    ],
                                    [
                                        "lenguage_id" => 3,
                                        "name" => "Acesso ao nosso refúgio de montanha",
                                        "description" => null
                                    ]
                                ]
                            ],
                            [
                                "icon_id" => null,
                                "order" => null,
                                "icon" => '$check',
                                "con_trf" => 1,
                                "characteristics" => [],
                                "translables" => [
                                    [
                                        "lenguage_id" => 1,
                                        "name" => "CON TRASLADO: Inicio en su hotel de El Calafate",
                                        "description" => null
                                    ],
                                    [
                                        "lenguage_id" => 2,
                                        "name" => "WITH TRANSFER: Start at your hotel in El Calafate",
                                        "description" => null
                                    ],
                                    [
                                        "lenguage_id" => 3,
                                        "name" => "COM TRANSFER: Comece no seu hotel em El Calafate",
                                        "description" => null
                                    ]
                                ]
                            ],
                            [
                                "icon_id" => null,
                                "order" => null,
                                "icon" => '$check',
                                "con_trf" => 1,
                                "characteristics" => [],
                                "translables" => [
                                    [
                                        "lenguage_id" => 1,
                                        "name" => "CON TRASLADO: Traslado con guía y visita de aproximadamente una hora a pasarelas",
                                        "description" => null
                                    ],
                                    [
                                        "lenguage_id" => 2,
                                        "name" => "WITH TRANSFER: Optional transfer with guide, including a visit of about one hour to the walkways",
                                        "description" => null
                                    ],
                                    [
                                        "lenguage_id" => 3,
                                        "name" => "COM TRANSFER: Traslado opcional, com guia e visita de aproximadamente uma hora às passarelas",
                                        "description" => null
                                    ]
                                ]
                            ],
                        ],
                        "translables" => []
                    ]
                ],
                "translables" => [
                    [
                        "lenguage_id" => 1,
                        "name" => "Detalle de compra",
                        "description" => ""
                    ],
                    [
                        "lenguage_id" => 2,
                        "name" => "Purchase detail",
                        "description" => ""
                    ],
                    [
                        "lenguage_id" => 3,
                        "name" => "Detalhe da compra",
                        "description" => ""
                    ]
                ]
            ];
        // 27 comparison_ratio
            $characteristics['characteristics'][] = [
                "icon_id" => null,
                "characteristic_type" => "comparison_ratio",
                "order" => null,
                "icon" => null,
                "characteristics" => [],
                "translables" => [
                    [
                        "lenguage_id" => "1",
                        "name" => "RATIO",
                        "description" => "Personalizado! 1 guía cada 10 personas"
                    ],
                    [
                        "lenguage_id" => "2",
                        "name" => "RATIO",
                        "description" => "Personalized! 1 guide every 10 people"
                    ],
                    [
                        "lenguage_id" => "3",
                        "name" => "RATIO",
                        "description" => "Personalizado! 1 guia a cada 10 pessoas"
                    ]
                ]
            ];
        // 28 comparison_total_walk
            $characteristics['characteristics'][] = [
                "icon_id" => null,
                "characteristic_type" => "comparison_total_walk",
                "order" => null,
                "icon" => null,
                "characteristics" => [],
                "translables" => [
                    [
                        "lenguage_id" => "1",
                        "name" => "Caminata total",
                        "description" => "3 km aprox"
                    ],
                    [
                        "lenguage_id" => "2",
                        "name" => "Caminata total",
                        "description" => "3 km aprox"
                    ],
                    [
                        "lenguage_id" => "3",
                        "name" => "Caminata total",
                        "description" => "3 km aprox"
                    ]
                ]
            ];
        // 29 comparison_waterfall_view
            $characteristics['characteristics'][] = [
                "icon_id" => null,
                "characteristic_type" => "comparison_waterfall_view",
                "order" => null,
                "icon" => null,
                "characteristics" => [],
                "translables" => [
                    [
                        "lenguage_id" => "1",
                        "name" => "Visita a Cascada",
                        "description" => "0"
                    ],
                    [
                        "lenguage_id" => "2",
                        "name" => "Visita a Cascada",
                        "description" => "0"
                    ],
                    [
                        "lenguage_id" => "3",
                        "name" => "Visita a Cascada",
                        "description" => "0"
                    ]
                ]
            ];
        return $characteristics;
    }

    public function minitrekking_2() // convert json to array IMPORTANT
    {
        $characteristics = [];

        //1 characteristics
            $characteristics['characteristics'][] = [
                # Generales"1"
                "icon_id" =>  NULL,
                "icon" =>  NULL,
                "characteristic_type" =>  "characteristics",
                "order" =>  NULL,
                #

                # translables
                    "translables" => [
                        [
                            "lenguage_id" =>  1,
                            "name" =>  "Características de la actividad",
                            "description" =>  NULL
                        ],
                        [
                            "lenguage_id" =>  2,
                            "name" =>  "Activity characteristic",
                            "description" =>  NULL
                        ],
                        [
                            "lenguage_id" =>  3,
                            "name" =>  "Característica da atividade",
                            "description" =>  NULL
                        ]
                    ],
                #

                # Las 6 características o ḿas
                #Translables
                "characteristics" =>
                [
                    #$clock
                        [
                            "icon" =>  '$clock',
                            "order" =>  "1",
                            "translables" =>  [
                                [
                                    #ESPAÑOL
                                    "lenguage_id" =>  "1",
                                    "name"        =>  '<p>9:30 horas (Todo el día)</p>',
                                    "description" =>  '<p>La duración de la actividad es de aproximadamente 9.30hs. Se recomienda no organizar otros planes para ese día.</p>'
                                ],
                                [
                                    # INGLES
                                    "lenguage_id" =>  "2",
                                    "name"        =>  "<p>9.5 hours (Full day)</p>",
                                    "description" =>  "<p>The duration of the activity is approximately 9:30 a.m. It is recommended not to organize other plans for that day.</p>"
                                ],
                                [
                                    # PORTUGUÉS
                                    "lenguage_id" =>  "3",
                                    "name"        =>  "<p>9.30 horas (o dia inteiro)</p>",
                                    "description" =>  "<p>A duração da atividade é de aproximadamente 9h30. Recomenda-se não organizar outros planos para esse dia.</p>"
                                ]
                            ]
                        ],
                    #$calendar
                        [
                            "icon" =>  '$calendar',
                            "order" =>  "2",
                            "translables" =>  [
                                [
                                #ESPAÑOL
                                    "lenguage_id" =>  "1",
                                    "name"        =>  "<p>1 Febrero al 30 Abril</p>",
                                    "description" =>  "<p>La disponibilidad de esta excursión es del 1 de Febrero al 30 de Abril</p>"
                                ],
                                [
                                # INGLES
                                    "lenguage_id" =>  "2",
                                    "name"        =>  "<p>February 1th to April 30th</p>",
                                    "description" =>  "<p>It is available from February 1 until the end of April</p>"
                                ],
                                [
                                # PORTUGUÉS
                                    "lenguage_id" =>  "3",
                                    "name"        =>  "<p>1 Fevereiro até 30 abril</p>",
                                    "description" =>  "<p>A excursão é disponível a partir do 1º dia de fevereiro até o fim de abril</p>"
                                ]
                            ]
                        ],
                    #$bus
                        [
                            "icon" =>  '$bus',
                            "order" =>  "3",
                            "translables" =>  [
                                [
                                #ESPAÑOL
                                    "lenguage_id" =>  "1",
                                    "name"        =>  '<p>Traslado opcional</p>',
                                    "description" =>  '<p>Opcional traslado con guía bilingüe y visita de una hora aproximadamente a pasarelas.</p>'
                                ],
                                [
                                # INGLES
                                    "lenguage_id" =>  "2",
                                    "name"        =>  '<p>Optional transfer</p>',
                                    "description" =>  "<p>Option with transfer, including bilingual guide and a visit of about one hour to the walkways.</p>"
                                ],
                                [
                                # PORTUGUÉS
                                    "lenguage_id" =>  "3",
                                    "name"        =>  '<p>Transferência opcional</p>',
                                    "description" =>  "<p>Traslado opcional, com guia bilíngue e visita de aproximadamente uma hora às passarelas.</p>"
                                ]
                            ]
                        ],
                    #$guide
                        [
                            "icon" =>  '$guide',
                            "order" =>  "4",
                            "translables" =>  [
                                [
                                #ESPAÑOL
                                    "lenguage_id" =>  "1",
                                    "name"        =>  "<p>Guías español e inglés.</p>",
                                    "description" =>  "<p>Nuestros guías hablan español e inglés.</p>"
                                ],
                                [
                                # INGLES
                                    "lenguage_id" =>  "2",
                                    "name"        =>  "<p>Spanish and English guides.</p>",
                                    "description" =>  "<p>Our guides speak Spanish and English.</p>"
                                ],
                                [
                                # PORTUGUÉS
                                    "lenguage_id" =>  "3",
                                    "name"        =>  "<p>Guias em espanhol e inglês</p>",
                                    "description" =>  "<p>Nossos guias falam espanhol e inglês.</p>"
                                ]
                            ]
                        ],
                    #$age
                        [
                            "icon" =>  '$age',
                            "order" =>  "5",
                            "translables" =>  [
                                [
                                #ESPAÑOL
                                    "lenguage_id" =>  "1",
                                    "name"        =>  "<p>Apto para 18 a 55 años</p>",
                                    "description" =>  '<p>Solo apto para <span style="color: #366895;">personas de 18 a 55 años.</span> Sin excepción.</p>'
                                ],
                                [
                                # INGLES
                                    "lenguage_id" =>  "2",
                                    "name"        =>  "<p>Suitable for 18 to 55 years old</p>",
                                    "description" =>  '<p>For <span style="color: #366895;">people between 18 and 55 years ONLY.</span> No exceptions.</p>'
                                ],
                                [
                                # PORTUGUÉS
                                    "lenguage_id" =>  "3",
                                    "name"        =>  "<p>Adequado para 18 a 55 anos</p>",
                                    "description" =>  '<p>Somente apto para <span style="color: #366895;">pessoas entre 18 e 55 anos.</span> Sem exceções.</p>'
                                ]
                            ]
                        ],
                        [
                            "icon" =>  '$walking',
                            "order" =>  "6",
                            "translables" =>  [
                                [
                                    #ESPAÑOL
                                    "lenguage_id" =>  "1",
                                    "name"        =>  "<p>No apto para personas sedentarias</p>",
                                    "description" =>  "<p>No apto para personas sedentarias</p>"
                                ],
                                [
                                    # INGLES
                                    "lenguage_id" =>  "2",
                                    "name"        =>  "<p>Not suitable for sedentary people</p>",
                                    "description" =>  "<p>Not suitable for sedentary people</p>"
                                ],
                                [
                                    # PORTUGUÉS
                                    "lenguage_id" =>  "3",
                                    "name"        =>  "<p>Não apta para pessoas sedentárias</p>",
                                    "description" =>  "<p>Não apta para pessoas sedentárias</p>"
                                ]
                            ]
                        ],
                    #$complexity
                        [
                            "icon" =>  '$experience',
                            "order" =>  "7",
                            "translables" =>  [
                                [
                                #ESPAÑOL
                                    "lenguage_id" =>  "1",
                                    "name"        =>  "<p>Complejidad moderada/alta</p>",
                                    "description" =>  "<p>Para que tengas una excelente experiencia en el Glaciar debés tener la capacidad psicofísica suficiente para caminar de manera constante al menos 3 horas, siendo parte del trayecto sobre el hielo con crampones.</p>"
                                ],
                                [
                                # INGLES
                                    "lenguage_id" =>  "2",
                                    "name"        =>  "<p>Moderate/high difficulty</p>",
                                    "description" =>  "<p>In order to have a great experience on the glacier, you should have the mental and physical capacity required to walk non-stop for 3 hours, partly on ice and with crampons.</p>"
                                ],
                                [
                                # PORTUGUÉS
                                    "lenguage_id" =>  "3",
                                    "name"        =>  "<p>Moderada/Alta complexidade</p>",
                                    "description" =>  "<p>Para disfrutar de uma experiência excelente no Glaciar, é imprescindível contar com capacidade psicofísica suficiente para pelo menos 3 horas de caminhada constante, com uma parte do percorrido sobre gelo e com grampos.</p>"
                                ]
                            ]
                        ],
                    #$does_not_include
                        [
                            "icon" =>  '$complexity',
                            "order" =>  "8",
                            "translables" =>  [
                                [
                                #ESPAÑOL
                                    "lenguage_id" =>  "1",
                                    "name"        =>  "<p>Items no incluidos</p>",
                                    "description" =>  "<p><strong>No incluye:</strong> Entrada al Parque Nacional | Comida y bebida | Ropa personal adecuada a las condiciones climáticas de la región. (frío, lluvia, viento, nieve)</p>"
                                ],
                                [
                                # INGLES
                                    "lenguage_id" =>  "2",
                                    "name"        =>  "<p>This tour does not include</p>",
                                    "description" =>  "<p><strong>Not included:</strong> Tickets for the national park | Food and drinks | Personal clothes adequate for the weather conditions of the area (cold, rain, wind, snow)</p>"
                                ],
                                [
                                # PORTUGUÉS
                                    "lenguage_id" =>  "3",
                                    "name"        =>  "<p>Itens não inclusos</p>",
                                    "description" =>  "<p><strong>Não inclui:</strong> Ingresso ao Parque Nacional | Comida e bebida | Roupa pessoal adequada para as condições climáticas próprias da região. (Frio, chuva, vento e neve)</p>"
                                ]
                            ]
                        ]
                ]
            ];

        //2 about
            $characteristics['characteristics'][] = [
                    "icon_id" => null,
                    "icon" => null,
                    "characteristic_type" =>  "about",
                    "order" => null,

                    "characteristics" => [],
                    "translables" => [
                        [
                            "lenguage_id" => 1,
                            "name" => "Sobre esta experiencia",
                            "description" => '<p>La excursi&oacute;n comienza con la b&uacute;squeda de los pasajeros en El Calafate. En nuestros confortables buses, camino al Parque Nacional Los Glaciares, los gu&iacute;as de turismo les brindar&aacute;n informaci&oacute;n sobre el lugar, el glaciar y la excursi&oacute;n.</p>
                            <p>&nbsp;</p>
                            <p>Una vez en el Puerto &ldquo;Bajo de las Sombras&rdquo; (Ruta 11, a 70 km de El Calafate) se embarca para cruzar el Lago Rico, llegando a la costa opuesta luego de aproximadamente 10 minutos de navegaci&oacute;n frente a la imponente pared sur del Glaciar Perito Moreno.</p>
                            <p>&nbsp;</p>
                            <p>Al desembarcar en la Bah&iacute;a Puma, a solo 500mts del Glaciar, ser&aacute;n recibidos por nuestros expertos gu&iacute;as de monta&ntilde;a. A partir de aqu&iacute;, comenzaremos el recorrido con una caminata de una hora aproximadamente por la costa del lago y luego por la morena al lado del hielo (terreno de rocas y tierra inestable).</p>
                            <p>&nbsp;</p>
                            <p>Al llegar al borde del glaciar, con las sorprendentes tonalidades azules del hielo, se organizar&aacute;n subgrupos de un m&aacute;ximo de 20 personas cada uno y se les colocar&aacute;n los crampones y cascos provistos por la empresa a pocos metros de la hermosa Cascada de las Cotorras. Esta excursi&oacute;n es altamente personalizada (un gu&iacute;a cada 10 pasajeros m&aacute;ximo). Una vez sobre el glaciar, recibir&aacute;n una charla de seguridad y exploraremos juntos durante una hora aprox. este para&iacute;so helado, declarado Patrimonio de la Humanidad (1981).</p>
                            <p>&nbsp;</p>
                            <p><span style="color: rgb(36, 113, 185);"><strong>El circuito sobre el glaciar es de dificultad media/alta, la superficie del hielo es irregular pero firme y segura.&nbsp;El ritmo de marcha es constante y se realizan pocas paradas. Se recorren 5km aprox. en terreno de rocas, tierra y hielo con crampones (El mini normal es 3km de recorrido) El tiempo de caminata total es de 3 horas aproximadamente.&nbsp;</strong></span></p>
                            <p><span style="color: rgb(36, 113, 185);"><strong>Es decir, se camina el doble de distancia en el mismo tiempo que en el Minitrekking com&uacute;n.</strong></span></p>
                            <p>&nbsp;</p>
                            <p>Durante la caminata se podr&aacute; apreciar las formaciones t&iacute;picas y cambiantes de un glaciar como profundas grietas, sumideros azules, enormes seracs y lagunas turquesas. Siempre acompa&ntilde;ados del sonido &uacute;nico de los crampones clav&aacute;ndose en el hielo.</p>
                            <p>&nbsp;</p>
                            <p>Al finalizar la caminata sobre el hielo, se visitar&aacute; la base del salto de agua, desde donde caminaremos de regreso por la morena lateral hasta llegar nuevamente a la Bah&iacute;a Puma. Una vez all&iacute;, embarcaremos de regreso hacia el Puerto Bajo de las Sombras, siempre mirando la pared de hielo por si nos sorprende con un estruendoso desprendimiento.</p>
                            <p>&nbsp;</p>
                            <p>La duraci&oacute;n de la excursi&oacute;n con el traslado desde El Calafate, es de 9:30 horas aproximadamente e incluye la visita de alrededor de 1 hora a las pasarelas del Glaciar Perito Moreno, ubicadas a 7 km de nuestro Puerto. All&iacute; podr&aacute;n disfrutar de la espectacular vista panor&aacute;mica del glaciar y recorrer alguno de los senderos autoguiados. En caso de no optar por nuestro transporte e ir por sus propios medios, esta excursi&oacute;n dura 4 h aprox., saliendo desde el Puerto y regresando al mismo punto de partida.</p>
                            <p>&nbsp;</p>
                            <p><span style="color: rgb(36, 113, 185);"><strong>Esta excursi&oacute;n se realiza en un ambiente natural por lo cual las condiciones clim&aacute;ticas y caracter&iacute;sticas del glaciar y sus alrededores cambian diariamente. </strong></span></p>
                            <p><span style="color: rgb(36, 113, 185);"><strong>Sin embargo, no se suspende, mientras que las condiciones de seguridad lo permitan. &iexcl;Los esperamos!</strong></span></p>'
                        ],
                        [
                            "lenguage_id" => 2,
                            "name" => "About",
                            "description" => '<p style="margin: 0cm 0cm 12pt; text-align: justify; line-height: 107%; font-size: 11pt; font-family: Calibri, sans-serif;"><span style="font-family: Stem-ExtraLight, sans-serif;">The tour starts when passengers are picked up in El Calafate. On our comfortable buses, on the way to Parque Nacional Los Glaciares, our tour guides will give you information on the place, the glacier and the tour.<br /><br /></span><span style="font-family: Stem-ExtraLight, sans-serif;">Once you arrive at &ldquo;Bajo de las Sombras&rdquo; port (located on Route 11, 70 Km from El Calafate), you will board a ship to cross Lago Rico and arrive to the opposite coast after a 10-minute navigation in front of the stunning south face of Glaciar Perito Moreno.<br /><br /></span><span style="font-family: Stem-ExtraLight, sans-serif;">When we disembark on Bahia Puma, just 500 meters from the glacier, you will be received by our expert mountain guides. Then we will start the hike with a walk of about one hour on the lake coast and later on the moraine next to the ice (ground with rocks and unstable earth). <br /><br /></span><span style="font-family: Stem-ExtraLight, sans-serif;">When we reach the glacier side, with its amazing blue ice shades, you will be divided into subgroups of up to 20 people each and you will have the crampons and helmets fitted, which are provided by the company, a few meters from the beautiful waterfall Cascada de las Cotorras. This tour is highly personalized (one guide every 10 passengers maximum). Once on the glacier, you will hear the safety instructions and we will explore together for about an hour this frozen paradise, which has been declared a World Heritage Site in 1981.<br /><br /></span><strong><span style="font-family: Stem-ExtraLight, sans-serif; color: #3686c3;">The level of difficulty of the circuit on the glacier is moderate/high. The ice surface is irregular, but firm and safe.&nbsp;The pace of the walk is continuous and there are few stops. You will walk for about 5 km. of land with rocks, earth and ice with crampons (The regular Minitrekking goes over 3 km.) The complete trekking takes about 3 hours.&nbsp; <br /><br /></span></strong><strong><span style="font-family: Stem-ExtraLight, sans-serif; color: #3686c3;">That is to say, you walk twice the distance of the regular Minitrekking in the same time.<br /><br /></span></strong><span style="font-family: Stem-ExtraLight, sans-serif;">During the trekking, you will be able to see the typical and changing glacier formations, such as deep cracks, blue moulins, huge seracs and turquoise ponds. All the way you will hear the unique sound of the crampons sticking into the ice.<br /><br /></span><span style="font-family: Stem-ExtraLight, sans-serif;">At the end of the trekking on the ice, we will visit the waterfall base, from where we will walk back along the lateral moraine until we reach Bah&iacute;a Puma again. Once we are there, we will take the boat back to &ldquo;Bajo de las Sombras&rdquo; port, always looking at the ice wall in case we see a noisy calving. <br /><br /></span><span style="font-family: Stem-ExtraLight, sans-serif;">The duration of this tour is about 9.5 hours, including the transfer and a one-hour guided visit to the walkways of Glaciar Perito Moreno, 7 km from our port. There, you will have the chance to enjoy the spectacular panoramic view of the glacier and walk along some of the self-guided paths. If you don&rsquo;t book our transfer and go by your own means, this tour takes about four hours, leaving from the port and returning to the same point.<br /><br /></span><strong><span style="font-family: Stem-ExtraLight, sans-serif; color: #2471b9;">This tour is carried out in a natural environment, so weather conditions and the glacier and its surroundings change every day. However, as long as it is safe to go on the tour, it is not suspended. We are waiting for you!</span></strong></p>'
                        ],
                        [
                            "lenguage_id" => 3,
                            "name" => "Sobre esta experiência",
                            "description" => '<p style="margin: 0cm 0cm 12pt; text-align: justify; line-height: 107%; font-size: 11pt; font-family: Calibri, sans-serif;"><span style="font-family: Stem-ExtraLight, sans-serif;">A excurs&atilde;o come&ccedil;a com a retirada dos passageiros na cidade de El Calafate. Em nossos confort&aacute;veis &ocirc;nibus, caminho ao Parque Nacional Los Glaciares, os guias de turismo oferecer&atilde;o informa&ccedil;&otilde;es sobre o local, a geleira e a excurs&atilde;o.<br /><br /></span><span style="font-family: Stem-ExtraLight, sans-serif;">Ao chegar ao porto &ldquo;Bajo de las Sombras&rdquo; (Ruta 11, a 70 km de El Calafate), come&ccedil;a a navega&ccedil;&atilde;o em barco, atravessando o Lago Rico at&eacute; atingir a margem oposta, logo ap&oacute;s 10 minutos de navega&ccedil;&atilde;o com vista para a imponente parede sul do Glaciar Perito Moreno.<br /><br /></span><span style="font-family: Stem-ExtraLight, sans-serif;">Ao desembarcar na Bahia Puma, apenas 500 metros da geleira, os passageiros ser&atilde;o recebidos por nossos espertos guias de montanha. O percorrido come&ccedil;a com uma caminhada de perto de uma hora pela margem do lago e, depois, pela morena ao lado do gelo (superf&iacute;cie inst&aacute;vel e rochosa).<br /><br /></span><span style="font-family: Stem-ExtraLight, sans-serif;">Ao chegar &agrave; borda do glaciar, no meio de uma paisagem de incr&iacute;veis tonalidades de gelo azuis, ser&atilde;o organizados subgrupos com at&eacute; 20 pessoas cada um, e ser&atilde;o colocados os grampos e capacetes fornecidos pela empresa, perto da bonita cachoeira chamada Cascada de las Cotorras. A excurs&atilde;o &eacute; muito personalizada (com um guia cada, no m&aacute;ximo, 10 passageiros). J&aacute; na geleira, os passageiros receber&atilde;o instru&ccedil;&otilde;es sobre seguran&ccedil;a e caminhar&atilde;o perto de uma hora para desfrutar desse para&iacute;so gelado, que foi declarado Patrim&ocirc;nio da Humanidade em 1981.<br /><br /></span><strong><span style="font-family: Stem-ExtraLight, sans-serif; color: #3686c3;">O circuito sobre a geleira tem uma dificuldade m&eacute;dia/alta; a superf&iacute;cie do gelo &eacute; irregular, mas firme e segura.&nbsp;O ritmo da caminhada &eacute; constante e com poucas pausas para descansar. A dist&acirc;ncia percorrida &eacute; de perto de 5 km de pedras, terra e gelo com grampos (A dist&acirc;ncia do Minitrekking normal &eacute; de 3 km). O tempo de caminhada total &eacute; de aproximadamente 3 horas.<br /><br /></span></strong><strong><span style="font-family: Stem-ExtraLight, sans-serif; color: #3686c3;">Ou seja, os passageiros v&atilde;o caminhar uma dist&acirc;ncia duas vezes maior do que no Minitrekking comum, mas no mesmo tempo.<br /><br /></span></strong><span style="font-family: Stem-ExtraLight, sans-serif;">No percorrido, os passageiros poder&atilde;o apreciar as forma&ccedil;&otilde;es caracter&iacute;sticas e em transforma&ccedil;&atilde;o de uma geleira, como profundas fendas, sumidouros azuis, enormes seracs e lagoas de cor azul turquesa. Sempre acompanhados pelo som &uacute;nico dos grampos que se cravam no gelo.<br /><br /></span><span style="font-family: Stem-ExtraLight, sans-serif;">Ao concluir a caminhada no gelo, os passageiros visitar&atilde;o a base da queda d&acute;&aacute;gua e, a partir da&iacute;, retornar&atilde;o caminhando pela morena lateral at&eacute; chegarem outra vez &agrave; Bahia Puma. Na Bahia, embarcar&atilde;o para o Porto Bajo de las Sombras, sempre olhando para a parede de gelo, caso aconte&ccedil;a um estrondoso e surpreendente desprendimento.<br /><br /></span><span style="font-family: Stem-ExtraLight, sans-serif;">A dura&ccedil;&atilde;o da excurs&atilde;o mais o traslado de El Calafate &eacute; de aproximadamente 9:30 horas. A excurs&atilde;o inclui uma visita de perto de uma hora &agrave;s passarelas do Glaciar Perito Moreno, a 7 km de nosso porto. L&aacute; poder&atilde;o desfrutar da espetacular vista panor&acirc;mica do glaciar e percorrer algumas das trilhas autoguiadas. Se o passageiro n&atilde;o escolher nosso transporte e decidir utilizar seus pr&oacute;prios meios, a dura&ccedil;&atilde;o da excurs&atilde;o &eacute; de quatro horas aproximadamente, saindo do porto e voltando para o mesmo ponto de sa&iacute;da.<br /><br /><br /></span><strong><span style="font-family: Stem-ExtraLight, sans-serif; color: #2471b9;">A excurs&atilde;o &eacute; realizada em um ambiente natural e com condi&ccedil;&otilde;es clim&aacute;ticas e caracter&iacute;sticas da geleira e de seu entorno que mudam todos os dias. No entanto, a excurs&atilde;o n&atilde;o ser&aacute; suspensa sempre que as condi&ccedil;&otilde;es de seguran&ccedil;a o permitirem. Esperamos voc&ecirc;s!</span></strong></p>'
                        ]
                    ]
            ];

        //3 before_buying
            $characteristics['characteristics'][] = [
                "icon_id" => null,
                "characteristic_type" => "before_buying",
                "order" => null,

                "characteristics" => [
                    [
                        "icon" => null,
                        "order" => null,
                        "characteristics" => [
                            [
                                "icon_id" => null,
                                "icon" => '$obesity',
                                "order" => null,

                                "characteristics" => [],
                                "translables" => [
                                    [
                                        "lenguage_id" => 1,
                                        "name" => null,
                                        "description" => "<li><strong>Personas sedentarias con obesidad.</strong> No podrán participar personas con obesidad. En el caso de los adultos, la Organización Mundial de la Salud (OMS) define que la obesidad es tal, cuando una persona presenta un Índice de Masa Corporal (IMC) igual o superior a 30. El IMC se calcula dividiendo el peso de una persona en kilos por el cuadrado de su talla en metros: (kg/m2). Ante cualquier duda o consulta, envíanos una mail a clientes@hieloyaventura.com</li>",
                                    ],
                                    [
                                        "lenguage_id" => 2,
                                        "name" => null,
                                        "description" => '<li><strong>Sedentary obese people.</strong> Obese people may not take the tour. The World Health Organization (WHO) determines an adult is obese when their body mass index (BMI) is equal to or higher than 30. The BMI is calculated by dividing an adult’s weight in kilograms by their square height in meters: (kg/m2). If you have any doubt or question, please contact us at clientes@hieloyaventura.com.</li>',
                                    ],
                                    [
                                        "lenguage_id" => 3,
                                        "name" => null,
                                        "description" => '<li><strong>Pessoas obesas e sedentárias.</strong> As pessoas obesas não poderão participar. No caso de adultos, a Organização Mundial da Saúde (OMS) define que uma pessoa é obesa quando seu Índice de Massa Corporal (IMC) é igual ou superior a 30. O IMC é calculado dividindo o peso do indivíduo em quilos pelo quadrado de sua altura em metros: (kg/m2). Se tiver alguma dúvida ou consulta, pode enviar um e-mail para clientes@hieloyaventura.com</li>'
                                    ]
                                ]
                            ],
                            [
                                "icon_id" => null,
                                "order" => null,
                                "icon" => '$pregnant',

                                "characteristics" => [],
                                "translables" => [
                                    [
                                        "lenguage_id" => 1,
                                        "name" => null,
                                        "description" => "<li>Embarazadas</li>",
                                    ],
                                    [
                                        "lenguage_id" => 2,
                                        "name" => null,
                                        "description" => "<li>Pregnant people</li>",
                                    ],
                                    [
                                        "lenguage_id" => 3,
                                        "name" => null,
                                        "description" => "<li>Grávidas</li>",
                                    ]
                                ]
                            ],
                            [
                                "icon_id" => null,
                                "order" => null,
                                "icon" => '$wheel_chair',

                                "characteristics" => [],
                                "translables" => [
                                    [
                                        "lenguage_id" => 1,
                                        "name" => null,
                                        "description" => "<li>Personas con cualquier grado o tipo de discapacidad física o mental que afecte su atención, marcha y/o coordinación.</li>",
                                    ],
                                    [
                                        "lenguage_id" => 2,
                                        "name" => null,
                                        "description" => "<li>People with any degree of physical or mental disability that affects their attention, ability to walk and/or coordination.</li>",
                                    ],
                                    [
                                        "lenguage_id" => 3,
                                        "name" => null,
                                        "description" => "<li>Pessoas com qualquer grau ou tipo de deficiência física ou mental que possa afetar sua atenção, marcha e/ou coordenação.</li>",
                                    ]
                                ]
                            ],
                            [
                                "icon_id" => null,
                                "order" => null,
                                "icon" =>  '$age_yellow',
                                "translables" =>  [
                                    [
                                    #ESPAÑOL
                                        "lenguage_id" =>  "1",
                                        "name"        =>  "<p>Apto para 18 a 55 años</p>",
                                        "description" =>  '<p>Solo apto para <span style="color: #366895;">personas de 18 a 55 años.</span> Sin excepción.</p>'
                                    ],
                                    [
                                    # INGLES
                                        "lenguage_id" =>  "2",
                                        "name"        =>  "<p>For people between 18 and 55 years only. No exceptions.</p>",
                                        "description" =>  '<p>Suitable for <span style="color: #366895;">people between 18 and 55 years ONLY.</span> No exceptions.</p>'
                                    ],
                                    [
                                    # PORTUGUÉS
                                        "lenguage_id" =>  "3",
                                        "name"        =>  "<p>Adequado para 18 a 55 anos</p>",
                                        "description" =>  '<p>Somente apto para <span style="color: #366895;">pessoas entre 18 e 55 anos.</span> Sem exceções.</p>'
                                    ]
                                ]
                            ],
                            [
                                "icon_id" => null,
                                "order" => null,
                                "icon" => '$heart_rate',

                                "characteristics" => [],
                                "translables" => [
                                    [
                                        "lenguage_id" => 1,
                                        "name" => null,
                                        "description" => "<li>Personas con antecedentes cardíacos. Personas que sufran enfermedades cardiovasculares centrales o periféricas, que sus capacidades cardíacas o vasculares se encuentren disminuidas, o utilicen stent, bypass, marcapasos u otras prótesis. Ejemplo: medicamentos anticoagulantes, varices grado III (las que se evidencian gruesas y múltiples).</li>",
                                    ],
                                    [
                                        "lenguage_id" => 2,
                                        "name" => null,
                                        "description" => "<li>People with a history of heart conditions, or suffer from central or peripheral heart or vascular diseases, whose heart or vascular capabilities are limited, or who have stents, a bypass, a pacemaker or other similar devices. For example: anticoagulant medication, stage 3 varicose veins (multiple apparent thick varicose veins).</li>",
                                    ],
                                    [
                                        "lenguage_id" => 3,
                                        "name" => null,
                                        "description" => "<li>Pessoas com problemas cardíacos. Pessoas com doenças cardiovasculares centrais ou periféricas, com capacidades cardíacas ou vasculares deficientes, ou quando utilizem stent, bypass, marca-passos ou outro tipo de prótese. Exemplo: medicamentos anti-coagulantes, varizes grau III (grossas e múltiplas).</li>",
                                    ]
                                ]
                            ],
                            [
                                "icon_id" => null,
                                "order" => null,
                                "icon" => '$lung',

                                "characteristics" => [],
                                "translables" => [
                                    [
                                        "lenguage_id" => 1,
                                        "name" => null,
                                        "description" => "<li>Personas que padezcan enfermedades provocadas POR discapacidades respiratorias como EPOC, asma, enfisema, entre otras.</li>",
                                    ],
                                    [
                                        "lenguage_id" => 2,
                                        "name" => null,
                                        "description" => "<li>People who suffer from diseases caused by respiratory impairment (COPD, asthma, emphysema, etc.).</li>",
                                    ],
                                    [
                                        "lenguage_id" => 3,
                                        "name" => null,
                                        "description" => "<li>Pessoas com doenças provocadas por deficiências respiratórias (EPOC, asma, enfisema, etc.).</li>",
                                    ]
                                ]
                            ]
                        ],
                        "translables" => []
                    ],
                    // [ //esta característica no está en BIG ICE, pero si tiene otras. Tengo que preguntar por eso
                    //     "icon_id" => null,
                    //     "order" => null,
                    //     "icon" => null,

                    //     "characteristics" => [],
                    //     "translables" => [
                    //         [
                    //             "lenguage_id" => 1,
                    //             "name" => null,
                    //             "description" => "<p>Los niños deben tener la capacidad psicofísica suficiente de para caminar 3 horas, de las cuales 1 hora y media es sobre el hielo con crampones.</p>"
                    //         ]
                    //     ]
                    // ]
                ],
                "translables" => [
                    [
                        "lenguage_id" => 1,
                        "name" => "A TENER EN CUENTA ANTES DE COMPRAR",
                        "description" => "<p><strong>Debido al grado de esfuerzo y dificultad ( Media/Alta) de esta experiencia con terreno de rocas, tierra inestable y hielo irregular pero firme y seguro y con el solo objetivo de preservar la salud, no podrán participar:</strong></p>"
                    ],
                    [
                        "lenguage_id" => 2,
                        "name" => "BEFORE PURCHASING YOUR TICKETS, PLEASE TAKE INTO ACCOUNT THE FOLLOWING:",
                        "description" => "<p><strong>Due to the degree of effort and difficulty of this experience (moderate/high) on rocky ground with unstable earth and irregular ice, but firm and safe, and for the sole purpose of preserving visitors’ health, the following people won’t be able to participate:</strong></p>"
                    ],
                    [
                        "lenguage_id" => 3,
                        "name" => "LEVAR EM CONTA ANTES DE COMPRAR",
                        "description" => "<p><strong>Devido ao grau de esforço e dificuldade (Médio/Alto) desta experiência com terrenos rochosos, terrenos instáveis ​​e gelo irregular mas firme e seguro e com o único objetivo de preservar a saúde, não poderão participar:</strong></p>"
                    ]
                ]
            ];

        //5 itinerary
            $characteristics['characteristics'][] = [
                "icon_id" => null,
                "characteristic_type" => 'itinerary',
                "order" => null,

                "characteristics" => [
                    [
                        "icon_id" => null,
                        "order" => null,
                        "icon" => null,
                        "characteristics" => [
                            [
                                "icon_id" => null,
                                "order" => null,
                                "icon" => '$itinerary_point',
                                "characteristics" => [],
                                "translables" => [
                                    [
                                        "lenguage_id" => 1,
                                        "name" => "Salida de El Calafate",
                                        "description" => "70km al glaciar."
                                    ],
                                    [
                                        "lenguage_id" => 2,
                                        "name" => "Departure from El Calafate",
                                        "description" => "70km to the glacier"
                                    ],
                                    [
                                        "lenguage_id" => 3,
                                        "name" => "Saída de El Calafate",
                                        "description" => "70 km para o glaciar."
                                    ]
                                ]
                            ],
                            [
                                "icon_id" => null,
                                "order" => null,
                                "icon" => '$itinerary_ship',
                                "characteristics" => [],
                                "translables" => [
                                    [
                                        "lenguage_id" => 1,
                                        "name" => "Embarque en Puerto",
                                        "description" => "10min de navegación."
                                    ],
                                    [
                                        "lenguage_id" => 2,
                                        "name" => "Boarding at the port",
                                        "description" => "10-minute navigation."
                                    ],
                                    [
                                        "lenguage_id" => 3,
                                        "name" => "Embarque no Porto",
                                        "description" => "10 minutos de navegação."
                                    ]
                                ]
                            ],
                            [
                                "icon_id" => null,
                                "order" => null,
                                "icon" => '$itinerary_shoe',
                                "characteristics" => [],
                                "translables" => [
                                    [
                                        "lenguage_id" => 1,
                                        "name" => "Trekking por la costa, morena y glaciar",
                                        "description" => "3 horas."
                                    ],
                                    [
                                        "lenguage_id" => 2,
                                        "name" => "Trekking along the coast, moraine and glacier",
                                        "description" => "3 hours."
                                    ],
                                    [
                                        "lenguage_id" => 3,
                                        "name" => "Trekking pela borda, morena e geleira",
                                        "description" => "3 horas."
                                    ]
                                ]
                            ],
                            [
                                "icon_id" => null,
                                "order" => null,
                                "icon" => '$waterfall',
                                "characteristics" => [],
                                "translables" => [
                                    [
                                        "lenguage_id" => 1,
                                        "name" => "Visita a cascada",
                                        "description" => null
                                    ],
                                    [
                                        "lenguage_id" => 2,
                                        "name" => "Visit to waterfall",
                                        "description" => null
                                    ],
                                    [
                                        "lenguage_id" => 3,
                                        "name" => "Visita à cachoeira",
                                        "description" => null
                                    ]
                                ]
                            ],
                            [
                                "icon_id" => null,
                                "order" => null,
                                "icon" => '$itinerary_ship',
                                "characteristics" => [],
                                "translables" => [
                                    [
                                        "lenguage_id" => 1,
                                        "name" => "Regreso al Puerto",
                                        "description" => "30 minurtos de navegación."
                                    ],
                                    [
                                        "lenguage_id" => 2,
                                        "name" => "Return to the port",
                                        "description" => "30min of navigation."
                                    ],
                                    [
                                        "lenguage_id" => 3,
                                        "name" => "Retorno para o Porto",
                                        "description" => "30min de navegação."
                                    ]
                                ]
                            ],
                            [
                                "icon_id" => null,
                                "order" => null,
                                "icon" => '$blue_stairs',
                                "characteristics" => [],
                                "translables" => [
                                    [
                                        "lenguage_id" => 1,
                                        "name" => "Visita a Pasarelas",
                                        "description" => "1hs aproximadamente"
                                    ],
                                    [
                                        "lenguage_id" => 2,
                                        "name" => "Visit to the walkways",
                                        "description" => "About 1 hour"
                                    ],
                                    [
                                        "lenguage_id" => 3,
                                        "name" => "Visita às passarelas",
                                        "description" => "1 hora aproximadamente"
                                    ]
                                ]
                            ],
                            [
                                "icon_id" => null,
                                "order" => null,
                                "icon" => '$itinerary_point',
                                "characteristics" => [],
                                "translables" => [
                                    [
                                        "lenguage_id" => 1,
                                        "name" => "Regreso a El Calafate",
                                        "description" => "70 km"
                                    ],
                                    [
                                        "lenguage_id" => 2,
                                        "name" => "Return to El Calafate",
                                        "description" => "70 km"
                                    ],
                                    [
                                        "lenguage_id" => 3,
                                        "name" => "Retorno para El Calafate",
                                        "description" => "70 km"
                                    ]
                                ]
                            ]
                        ],
                        "translables" => []
                    ],
                    [
                        "icon_id" => null,
                        "order" => null,
                        "icon" => null,
                        "characteristics" => [
                            [
                                "icon_id" => null,
                                "order" => null,
                                "icon" => null,
                                "characteristics" => [],
                                "translables" => [
                                    [
                                        "lenguage_id" => 1,
                                        "name" => "OPCIONAL CON TRASLADO",
                                        "description" => null
                                    ],
                                    [
                                        "lenguage_id" => 2,
                                        "name" => "OPTIONAL WITH TRANSFER",
                                        "description" => null
                                    ],
                                    [
                                        "lenguage_id" => 3,
                                        "name" => "OPCIONAL COM TRANSFER",
                                        "description" => null
                                    ]
                                ]
                            ],
                            [
                                "icon_id" => null,
                                "order" => null,
                                "icon" => null,
                                "characteristics" => [],
                                "translables" => [
                                    [
                                        "lenguage_id" => 1,
                                        "name" => "TOUR INCLUIDO",
                                        "description" => null
                                    ],
                                    [
                                        "lenguage_id" => 2,
                                        "name" => "TOUR INCLUDED",
                                        "description" => null
                                    ],
                                    [
                                        "lenguage_id" => 3,
                                        "name" => "PASSEIO INCLUÍDO",
                                        "description" => null
                                    ]
                                ]
                            ]
                        ],
                        "translables" => []
                    ]
                ],
                "translables" => [
                    [
                        "lenguage_id" => 1,
                        "name" => "Itinerario Minitrekking 2",
                        "description" => null
                    ],
                    [
                        "lenguage_id" => 2,
                        "name" => "Minitrekking 2 itinerary",
                        "description" => null
                    ],
                    [
                        "lenguage_id" => 3,
                        "name" => "Itinerário Minitrekking 2",
                        "description" => null
                    ]
                ]
            ];

        //7 carry
            $characteristics['characteristics'][] =
                [
                    "icon_id" => null,
                    "characteristic_type" => "carry",
                    "order" => null,
                    "icon" => null,
                    "characteristics" => [
                        [
                            "icon_id" => null,
                            "order" => null,
                            "icon" => '$cloth',
                            "characteristics" => [],
                            "translables" => [
                                [
                                    "lenguage_id" => 1,
                                    "name" => null,
                                    "description" => "<p>Vestir ropa cómoda y abrigada. Campera y pantalón impermeable, botas de trekking impermeables o calzado deportivo con cordones. Mochila, lentes de sol, protector solar, guantes y gorro. El clima es cambiante y hay que estar preparado para no mojarse ni pasar frío.</p>"
                                ],
                                [
                                    "lenguage_id" => 2,
                                    "name" => null,
                                    "description" => "<p>Wear comfortable and warm clothes. Rain jacket and waterproof trousers, waterproof trekking boots or sports shoes with laces. Backpack, sunglasses, sunscreen, gloves and hat. You must be prepared for the changing weather conditions, so that you don’t get wet or cold.</p>"
                                ],
                                [
                                    "lenguage_id" => 3,
                                    "name" => null,
                                    "description" => "<p>Vestir roupa confortável e quente. Casaco, calças e botas de trekking impermeáveis, ou calçado esportivo com cordão. Mochila, óculos de sol, protetor solar, luvas e gorro. Como o clima é cambiante, é preciso estar preparado para não se molhar nem passar frio.</p>"
                                ]
                            ]
                        ],
                        [
                            "icon_id" => null,
                            "order" => null,
                            "icon" => '$food',
                            "characteristics" => [],
                            "translables" => [
                                [
                                    "lenguage_id" => 1,
                                    "name" => null,
                                    "description" => "<p>Llevar comida y bebida para el d&iacute;a. La Empresa no cuenta con servicio de venta de comidas ni bebidas.</p>"
                                ],
                                [
                                    "lenguage_id" => 2,
                                    "name" => null,
                                    "description" => "<p>Take some food and drink for the day. The company does not sell food and drink</p>"
                                ],
                                [
                                    "lenguage_id" => 3,
                                    "name" => null,
                                    "description" => "<p>Levar comida e bebida para todo o dia. A empresa não oferece serviço de venda de comidas nem bebidas.</p>"
                                ]
                            ]
                        ],
                        [
                            "icon_id" => null,
                            "characteristic_type" => null,
                            "order" => null,
                            "icon" => '$ticket',
                            "characteristics" => [],
                            "translables" => [
                                [
                                    "lenguage_id" => 1,
                                    "name" => null,
                                    "description" => '<p>Deber&aacute;s presentar tu entrada al Parque Nacional. Pod&eacute;s comprarla&nbsp;<a href="https://ventaweb.apn.gob.ar/reserva/inicio?dp=05" target="_blank" rel="noopener"><strong>a<span style="color: #2471B9;">c&aacute;</span><span style="color: #2471B9;"> (Seleccionar: &ldquo;Acceso Corredor Rio Mitre y Glaciar Perito Moreno&rdquo;)</span></strong></a>&nbsp;o abonarla en efectivo (en pesos argentinos) al llegar al Parque Nacional.</p>'
                                ],
                                [
                                    "lenguage_id" => 2,
                                    "name" => null,
                                    "description" => '<p>You will have to exhibit your ticket for the national park. You can buy your ticket here&nbsp;<a href="https://ventaweb.apn.gob.ar/reserva/inicio?dp=05" target="_blank" rel="noopener"><strong>(<span style="color: #2471B9;">Select: &ldquo;Acceso Corredor Rio Mitre y Glaciar Perito Moreno&rdquo;)</span></strong></a>&nbsp;or pay it in cash (in Argentine pesos) when you arrive at the National Park.</p>'
                                ],
                                [
                                    "lenguage_id" => 3,
                                    "name" => null,
                                    "description" => '<p>Deve ser apresentado o comprovante de ingresso ao Parque Nacional. Pode comprar o ingresso aqui&nbsp;<span style="color: #2471B9;"><a style="color: #2471B9;" href="https://ventaweb.apn.gob.ar/reserva/inicio?dp=05" target="_blank" rel="noopener"><strong>(Selecionar: &ldquo;Acceso Corredor Rio Mitre y Glaciar Perito Moreno&rdquo;)</strong></a></span>&nbsp;ou pagar com dinheiro (pesos argentinos) ao chegar ao Parque Nacional.</p>'
                                ]
                            ]
                        ]
                    ],
                    "translables" => [
                        [
                            "lenguage_id" => 1,
                            "name" => "Qué llevar en la excursión?",
                            "description" => null
                        ],
                        [
                            "lenguage_id" => 2,
                            "name" => "What SHOULD I bring?",
                            "description" => null
                        ],
                        [
                            "lenguage_id" => 3,
                            "name" => "O que É PRECISO levar?",
                            "description" => null
                        ]
                    ]
                ];
        //9 restrictions
            $characteristics['characteristics'][] = [
                "icon_id" => null,
                "characteristic_type" => "restrictions",
                "order" => null,
                "icon" => null,

                "characteristics" => [],
                "translables" => [
                    [
                        "lenguage_id" => 1,
                        "name" => "Restricciones importantes antes de comprar",
                        "description" => '<p>Debido al grado de esfuerzo y dificultad que esta actividad presenta y con el solo objetivo de preservar la salud, no podrán participar de la excursión ciertas personas.</p>
                        <p style="text-align: justify;">&nbsp;</p>'
                    ],
                    [
                        "lenguage_id" => 2,
                        "name" => "Important restrictions before buying",
                        "description" => '<p>Due to the degree of effort and difficulty that this activity presents and with the sole objective of preserving health, people with:</p>
                        <p style="text-align: justify;">&nbsp;</p>'
                    ],
                    [
                        "lenguage_id" => 3,
                        "name" => "Restrições importantes antes de comprar",
                        "description" => '<p>Devido ao grau de esforço e dificuldade que esta atividade apresenta e com o único objetivo de preservar a saúde, as pessoas com:</p>
                        <p style="text-align: justify;">&nbsp;</p>'
                    ]
                ]
            ];

        //10 comparison_sail_perito
            $characteristics['characteristics'][] = [
                "icon_id" => null,
                "characteristic_type" => "comparison_sail_perito",
                "order" => null,
                "icon" => null,

                "characteristics" => [],
                "translables" => [
                    [
                        "lenguage_id" => "1",
                        "name" => "Navega frente al Glaciar Perito Moreno",
                        "description" => "1"
                    ],
                    [
                        "lenguage_id" => "2",
                        "name" => "Sail in front of the Perito Moreno Glacier",
                        "description" => "1"
                    ],
                    [
                        "lenguage_id" => "3",
                        "name" => "Navegue em frente ao Glaciar Perito Moreno",
                        "description" => "1"
                    ]
                ]
            ];
        //11 comparison_trekking_ice
            $characteristics['characteristics'][] = [
                "icon_id" => null,
                "characteristic_type" => "comparison_trekking_ice",
                "order" => null,
                "icon" => null,

                "characteristics" => [],
                "translables" => [
                    [
                        "lenguage_id" => "1",
                        "name" => "Trekking sobre hielo",
                        "description" => "1hr aproximadamente'"
                    ],
                    [
                        "lenguage_id" => "2",
                        "name" => "Ice trekking",
                        "description" => "Approximately 1hr"
                    ],
                    [
                        "lenguage_id" => "3",
                        "name" => "Trekking no gelo",
                        "description" => "Aproximadamente 1h'"
                    ]
                ]
            ];
        //12 comparison_dificult
            $characteristics['characteristics'][] = [
                "icon_id" => null,
                "characteristic_type" => "comparison_dificult",
                "order" => null,
                "icon" => null,

                "characteristics" => [],
                "translables" => [
                    [
                        "lenguage_id" => "1",
                        "name" => "Dificultad",
                        "description" => "Moderada/Alta"
                    ],
                    [
                        "lenguage_id" => "2",
                        "name" => "Difficulty",
                        "description" => "Moderate/High"
                    ],
                    [
                        "lenguage_id" => "3",
                        "name" => "Dificuldade",
                        "description" => "Moderada/Alta"
                    ]
                ]
            ];
        //14 comparison_fissures
            $characteristics['characteristics'][] = [
                "icon_id" => null,
                "characteristic_type" => "comparison_fissures",
                "order" => null,
                "icon" => null,

                "characteristics" => [],
                "translables" => [
                    [
                        "lenguage_id" => "1",
                        "name" => "Vista de grietas",
                        "description" => "1"
                    ],
                    [
                        "lenguage_id" => "2",
                        "name" => "View of cracks",
                        "description" => "1"
                    ],
                    [
                        "lenguage_id" => "3",
                        "name" => "Visão de rachaduras",
                        "description" => "1"
                    ]
                ]
            ];
        //15 comparison_seracs
            $characteristics['characteristics'][] = [
                "icon_id" => null,
                "characteristic_type" => "comparison_seracs",
                "order" => null,
                "icon" => null,

                "characteristics" => [],
                "translables" => [
                    [
                        "lenguage_id" => "1",
                        "name" => "Vista de Seracs",
                        "description" => "1"
                    ],
                    [
                        "lenguage_id" => "2",
                        "name" => "View of Seracs",
                        "description" => "1"
                    ],
                    [
                        "lenguage_id" => "3",
                        "name" => "Visão de Seracs",
                        "description" => "1"
                    ]
                ]
            ];
        //16 comparison_sinks
            $characteristics['characteristics'][] = [
                "icon_id" => null,
                "characteristic_type" => "comparison_sinks",
                "order" => null,
                "icon" => null,

                "characteristics" => [],
                "translables" => [
                    [
                        "lenguage_id" => "1",
                        "name" => "Vista de sumideros",
                        "description" => "1"
                    ],
                    [
                        "lenguage_id" => "2",
                        "name" => "View of sinkholes",
                        "description" => "1"
                    ],
                    [
                        "lenguage_id" => "3",
                        "name" => "Visão dos sumidouros",
                        "description" => "1"
                    ]
                ]
            ];
        //17 comparison_caves
            $characteristics['characteristics'][] = [
                "icon_id" => null,
                "characteristic_type" => "comparison_caves",
                "order" => null,
                "icon" => null,

                "characteristics" => [],
                "translables" => [
                    [
                        "lenguage_id" => "1",
                        "name" => "Vista de cuevas",
                        "description" => "eventualmente"
                    ],
                    [
                        "lenguage_id" => "2",
                        "name" => "View of caves",
                        "description" => "eventually"
                    ],
                    [
                        "lenguage_id" => "3",
                        "name" => "Vista das cavernas",
                        "description" => "eventualmente"
                    ]
                ]
            ];
        //18 comparison_laggons
            $characteristics['characteristics'][] = [
                "icon_id" => null,
                "characteristic_type" => "comparison_laggons",
                "order" => null,
                "icon" => null,
                "characteristics" => [],
                "translables" => [
                    [
                        "lenguage_id" => "1",
                        "name" => "Vista de lagunas",
                        "description" => "eventualmente"
                    ],
                    [
                        "lenguage_id" => "2",
                        "name" => "View of lagoons",
                        "description" => "eventually"
                    ],
                    [
                        "lenguage_id" => "3",
                        "name" => "Vista das lagoas",
                        "description" => "eventualmente"
                    ]
                ]
            ];
        //19 comparison_group_size
            $characteristics['characteristics'][] = [
                "icon_id" => null,
                "characteristic_type" => "comparison_group_size",
                "order" => null,
                "icon" => null,
                "characteristics" => [],
                "translables" => [
                    [
                        "lenguage_id" => "1",
                        "name" => "Tamaño de grupo",
                        "description" => "20 sobre el hielo"
                    ],
                    [
                        "lenguage_id" => "2",
                        "name" => "Group size",
                        "description" => "20 on ice"
                    ],
                    [
                        "lenguage_id" => "3",
                        "name" => "Tamanho do grupo",
                        "description" => "20 no gelo"
                    ]
                ]
            ];
        //20 comparison_lagoon_coast_trekking
            $characteristics['characteristics'][] = [
                "icon_id" => null,
                "characteristic_type" => "comparison_lagoon_coast_trekking",
                "order" => null,
                "icon" => null,
                "characteristics" => [],
                "translables" => [
                    [
                        "lenguage_id" => "1",
                        "name" => "Trekking por costa del lago",
                        "description" => "1"
                    ],
                    [
                        "lenguage_id" => "2",
                        "name" => "Trekking along the lake coast",
                        "description" => "1"
                    ],
                    [
                        "lenguage_id" => "3",
                        "name" => "Trekking ao longo da costa do lago",
                        "description" => "1"
                    ]
                ]
            ];
        //21 comparison_forest_trekking
            $characteristics['characteristics'][] = [
                "icon_id" => null,
                "characteristic_type" => "comparison_forest_trekking",
                "order" => null,
                "icon" => null,
                "characteristics" => [],
                "translables" => [
                    [
                        "lenguage_id" => "1",
                        "name" => "Trekking por bosque",
                        "description" => "0"
                    ],
                    [
                        "lenguage_id" => "2",
                        "name" => "Trekking through forest",
                        "description" => "0"
                    ],
                    [
                        "lenguage_id" => "3",
                        "name" => "Trekking pela floresta",
                        "description" => "0"
                    ]
                ]
            ];
        //22 comparison_food_included
            $characteristics['characteristics'][] = [
                "icon_id" => null,
                "characteristic_type" => "comparison_food_included",
                "order" => null,
                "icon" => null,
                "characteristics" => [],
                "translables" => [
                    [
                        "lenguage_id" => "1",
                        "name" => "Comida incluida",
                        "description" => "0"
                    ],
                    [
                        "lenguage_id" => "2",
                        "name" => "Lunch included",
                        "description" => "0"
                    ],
                    [
                        "lenguage_id" => "3",
                        "name" => "Almoço incluso",
                        "description" => "0"
                    ]
                ]
            ];
        //23 comparison_hotel_transfer
            $characteristics['characteristics'][] = [
                "icon_id" => null,
                "characteristic_type" => "comparison_hotel_transfer",
                "order" => null,
                "icon" => null,
                "characteristics" => [],
                "translables" => [
                    [
                        "lenguage_id" => "1",
                        "name" => "Traslado desde el hotel",
                        "description" => "optativo"
                    ],
                    [
                        "lenguage_id" => "2",
                        "name" => "Transfer from the hotel",
                        "description" => "optional"
                    ],
                    [
                        "lenguage_id" => "3",
                        "name" => "Traslado do hotel",
                        "description" => "opcional"
                    ]
                ]
            ];
        //25 comparison_current_price
            $characteristics['characteristics'][] = [
                "icon_id" => null,
                "characteristic_type" => "comparison_current_price",
                "order" => null,
                "icon" => null,
                "characteristics" => [],
                "translables" => [
                    [
                        "lenguage_id" => "1",
                        "name" => "Precio actual",
                        "description" => 210000
                    ],
                    [
                        "lenguage_id" => "2",
                        "name" => "Actual Price",
                        "description" => 210000
                    ],
                    [
                        "lenguage_id" => "3",
                        "name" => "Preço real",
                        "description" => 210000
                    ]
                ]
            ];

        //26 purchase_detail
            $characteristics['characteristics'][] = [
                "icon_id" => null,
                "characteristic_type" => 'purchase_detail',
                "order" => null,

                "characteristics" => [
                    [
                        "icon_id" => null,
                        "order" => null,
                        "icon" => null,
                        "characteristics" => [
                            [
                                "icon_id" => null,
                                "order" => null,
                                "icon" => '$check',
                                "con_trf" => null,
                                "characteristics" => [],
                                "translables" => [
                                    [
                                        "lenguage_id" => 1,
                                        "name" => "Guía español e inglés durante el recorrido en el glaciar",
                                        "description" => ""
                                    ],
                                    [
                                        "lenguage_id" => 2,
                                        "name" => "Spanish and English guide during the tour on the glacier",
                                        "description" => ""
                                    ],
                                    [
                                        "lenguage_id" => 3,
                                        "name" => "Guia espanhol e inglês durante o passeio pela geleira",
                                        "description" => ""
                                    ]
                                ]
                            ],
                            // [
                            //     "icon_id" => null,
                            //     "order" => null,
                            //     "icon" => '$check',
                            //     "con_trf" => 1,
                            //     "characteristics" => [],
                            //     "translables" => [
                            //         [
                            //             "lenguage_id" => 1,
                            //             "name" => "Visita de 1 hora a las pasarelas",
                            //             "description" => null
                            //         ],
                            //         [
                            //             "lenguage_id" => 2,
                            //             "name" => "1 hour visit to the catwalks",
                            //             "description" => null
                            //         ],
                            //         [
                            //             "lenguage_id" => 3,
                            //             "name" => "1 hora de visita às passarelas",
                            //             "description" => null
                            //         ]
                            //     ]
                            // ],
                            [
                                "icon_id" => null,
                                "order" => null,
                                "icon" => '$check',
                                "con_trf" => null,
                                "characteristics" => [],
                                "translables" => [
                                    [
                                        "lenguage_id" => 1,
                                        "name" => "Navegación frente a la pared del glaciar",
                                        "description" => null
                                    ],
                                    [
                                        "lenguage_id" => 2,
                                        "name" => "Navigation in front of the glacier wall",
                                        "description" => null
                                    ],
                                    [
                                        "lenguage_id" => 3,
                                        "name" => "Navegação em frente à parede da geleira",
                                        "description" => null
                                    ]
                                ]
                            ],
                            [
                                "icon_id" => null,
                                "order" => null,
                                "icon" => '$check',
                                "con_trf" => null,
                                "characteristics" => [],
                                "translables" => [
                                    [
                                        "lenguage_id" => 1,
                                        "name" => "Trekking sobre el hielo de 1 hora",
                                        "description" => null
                                    ],
                                    [
                                        "lenguage_id" => 2,
                                        "name" => "1 hour ice trekking",
                                        "description" => null
                                    ],
                                    [
                                        "lenguage_id" => 3,
                                        "name" => "1 hora de caminhada no gelo",
                                        "description" => null
                                    ]
                                ]
                            ],
                            [
                                "icon_id" => null,
                                "order" => null,
                                "icon" => '$check',
                                "con_trf" => null,
                                "characteristics" => [],
                                "translables" => [
                                    [
                                        "lenguage_id" => 1,
                                        "name" => "Acceso a nuestro refugio de montaña",
                                        "description" => null
                                    ],
                                    [
                                        "lenguage_id" => 2,
                                        "name" => "Access to our mountain refuge",
                                        "description" => null
                                    ],
                                    [
                                        "lenguage_id" => 3,
                                        "name" => "Acesso ao nosso refúgio de montanha",
                                        "description" => null
                                    ]
                                ]
                            ],
                            [
                                "icon_id" => null,
                                "order" => null,
                                "icon" => '$check',
                                "con_trf" => 1,
                                "characteristics" => [],
                                "translables" => [
                                    [
                                        "lenguage_id" => 1,
                                        "name" => "CON TRASLADO: Inicio en su hotel de El Calafate",
                                        "description" => null
                                    ],
                                    [
                                        "lenguage_id" => 2,
                                        "name" => "WITH TRANSFER: Start at your hotel in El Calafate",
                                        "description" => null
                                    ],
                                    [
                                        "lenguage_id" => 3,
                                        "name" => "COM TRANSFER: Comece no seu hotel em El Calafate",
                                        "description" => null
                                    ]
                                ]
                            ],
                            [
                                "icon_id" => null,
                                "order" => null,
                                "icon" => '$check',
                                "con_trf" => 1,
                                "characteristics" => [],
                                "translables" => [
                                    [
                                        "lenguage_id" => 1,
                                        "name" => "CON TRASLADO: Traslado con guía y visita de aproximadamente una hora a pasarelas",
                                        "description" => null
                                    ],
                                    [
                                        "lenguage_id" => 2,
                                        "name" => "WITH TRANSFER: Optional transfer with guide, including a visit of about one hour to the walkways",
                                        "description" => null
                                    ],
                                    [
                                        "lenguage_id" => 3,
                                        "name" => "COM TRANSFER: Traslado opcional, com guia e visita de aproximadamente uma hora às passarelas",
                                        "description" => null
                                    ]
                                ]
                            ],
                        ],
                        "translables" => []
                    ]
                ],
                "translables" => [
                    [
                        "lenguage_id" => 1,
                        "name" => "Detalle de compra",
                        "description" => ""
                    ],
                    [
                        "lenguage_id" => 2,
                        "name" => "Purchase detail",
                        "description" => ""
                    ],
                    [
                        "lenguage_id" => 3,
                        "name" => "Detalhe da compra",
                        "description" => ""
                    ]
                ]
            ];
        // 27 comparison_ratio
            $characteristics['characteristics'][] = [
                "icon_id" => null,
                "characteristic_type" => "comparison_ratio",
                "order" => null,
                "icon" => null,
                "characteristics" => [],
                "translables" => [
                    [
                        "lenguage_id" => "1",
                        "name" => "RATIO",
                        "description" => "Personalizado! 1 guía cada 10 personas"
                    ],
                    [
                        "lenguage_id" => "2",
                        "name" => "RATIO",
                        "description" => "Personalized! 1 guide every 10 people"
                    ],
                    [
                        "lenguage_id" => "3",
                        "name" => "RATIO",
                        "description" => "Personalizado! 1 guia a cada 10 pessoas"
                    ]
                ]
            ];
        // 28 comparison_total_walk
            $characteristics['characteristics'][] = [
                "icon_id" => null,
                "characteristic_type" => "comparison_total_walk",
                "order" => null,
                "icon" => null,
                "characteristics" => [],
                "translables" => [
                    [
                        "lenguage_id" => "1",
                        "name" => "Caminata total",
                        "description" => "4,5 km aprox"
                    ],
                    [
                        "lenguage_id" => "2",
                        "name" => "Caminata total",
                        "description" => "4,5 km aprox"
                    ],
                    [
                        "lenguage_id" => "3",
                        "name" => "Caminata total",
                        "description" => "4,5 km aprox"
                    ]
                ]
            ];
        // 29 comparison_waterfall_view
            $characteristics['characteristics'][] = [
                "icon_id" => null,
                "characteristic_type" => "comparison_waterfall_view",
                "order" => null,
                "icon" => null,
                "characteristics" => [],
                "translables" => [
                    [
                        "lenguage_id" => "1",
                        "name" => "Visita a Cascada",
                        "description" => "1"
                    ],
                    [
                        "lenguage_id" => "2",
                        "name" => "Visita a Cascada",
                        "description" => "1"
                    ],
                    [
                        "lenguage_id" => "3",
                        "name" => "Visita a Cascada",
                        "description" => "1"
                    ]
                ]
            ];
        return $characteristics;
    }

    public function bigIce()
    {
        $characteristics = [];

        //1 characteristics
            $characteristics['characteristics'][] = [
                # Generales"1"
                "icon_id" =>  NULL,
                "icon" =>  NULL,
                "characteristic_type" =>  "characteristics",
                "order" =>  NULL,
                #

                # translables
                    "translables" => [
                        [
                            "lenguage_id" =>  1,
                            "name" =>  "Características de la actividad",
                            "description" =>  NULL
                        ],
                        [
                            "lenguage_id" =>  2,
                            "name" =>  "Activity characteristic",
                            "description" =>  NULL
                        ],
                        [
                            "lenguage_id" =>  3,
                            "name" =>  "Característica da atividade",
                            "description" =>  NULL
                        ]
                    ],
                #

                # Las 6 características o ḿas
                #Translables
                "characteristics" =>
                [
                    #$clock
                        [
                            "icon" =>  '$clock',
                            "order" =>  "1",
                            "translables" =>  [
                                [
                                    #ESPAÑOL
                                    "lenguage_id" =>  "1",
                                    "name"        =>  '<p>12 horas (Día completo)</p>',
                                    "description" =>  '<p>La actividad es de todo el día, y cuenta con 7 horas y media de caminata.</p>'
                                ],
                                [
                                    # INGLES
                                    "lenguage_id" =>  "2",
                                    "name"        =>  "<p>12 hours (Full day)</p>",
                                    "description" =>  "<p>The activity is all day, and has 7 hours and a half of walking.</p>"
                                ],
                                [
                                    # PORTUGUÉS
                                    "lenguage_id" =>  "3",
                                    "name"        =>  "<p>12 horas (Dia inteiro)</p>",
                                    "description" =>  "<p>A atividade é o dia todo, e tem 7 horas e meia de caminhada.</p>"
                                ]
                            ]
                        ],
                    #$calendar
                        [
                            "icon" =>  '$calendar',
                            "order" =>  "2",
                            "translables" =>  [
                                [
                                #ESPAÑOL
                                    "lenguage_id" =>  "1",
                                    "name"        =>  "<p>15 septiembre al 30 abril</p>",
                                    "description" =>  "<p>La disponibilidad de esta excursión es del 15 de Septiembre al 30 de Abril</p>"
                                ],
                                [
                                # INGLES
                                    "lenguage_id" =>  "2",
                                    "name"        =>  "<p>September 15th to April 30th</p>",
                                    "description" =>  "<p>The availability of this excursion is from September 15 to April 30</p>"
                                ],
                                [
                                # PORTUGUÉS
                                    "lenguage_id" =>  "3",
                                    "name"        =>  "<p>15 Setembro até 30 abril</p>",
                                    "description" =>  "<p>A disponibilidade desta excursão é de 15 de setembro a 30 de abril</p>"
                                ]
                            ]
                        ],
                    #$bus
                        [
                            "icon" =>  '$bus',
                            "order" =>  "3",
                            "translables" =>  [
                                [
                                #ESPAÑOL
                                    "lenguage_id" =>  "1",
                                    "name"        =>  '<p>Traslado opcional</p>',
                                    "description" =>  '<p>Opcional traslado con guía y visita de una hora aproximadamente a pasarelas.</p>'
                                ],
                                [
                                # INGLES
                                    "lenguage_id" =>  "2",
                                    "name"        =>  '<p>Optional transfer</p>',
                                    "description" =>  "<p>Optional transfer with guide, including a visit of about one hour to the walkways.</p>"
                                ],
                                [
                                # PORTUGUÉS
                                    "lenguage_id" =>  "3",
                                    "name"        =>  '<p>Transferência opcional</p>',
                                    "description" =>  "<p>Traslado opcional, com guia e visita de aproximadamente uma hora às passarelas.</p>"
                                ]
                            ]
                        ],
                    #$guide
                        [
                            "icon" =>  '$guide',
                            "order" =>  "4",
                            "translables" =>  [
                                [
                                #ESPAÑOL
                                    "lenguage_id" =>  "1",
                                    "name"        =>  "<p>Guías español e inglés.</p>",
                                    "description" =>  "<p>Nuestros guías hablan español e inglés.</p>"
                                ],
                                [
                                # INGLES
                                    "lenguage_id" =>  "2",
                                    "name"        =>  "<p>Spanish and English guides.</p>",
                                    "description" =>  "<p>Our guides speak Spanish and English.</p>"
                                ],
                                [
                                # PORTUGUÉS
                                    "lenguage_id" =>  "3",
                                    "name"        =>  "<p>Guias em espanhol e inglês</p>",
                                    "description" =>  "<p>Nossos guias falam espanhol e inglês.</p>"
                                ]
                            ]
                        ],
                    #$age
                        [
                            "icon" =>  '$age',
                            "order" =>  "5",
                            "translables" =>  [
                                [
                                #ESPAÑOL
                                    "lenguage_id" =>  "1",
                                    "name"        =>  "<p>Apto para 18 a 50 años</p>",
                                    "description" =>  '<p>Solo apto para <span style="color: #366895;">personas de 18 a 50 años.</span> Sin excepción.</p>'
                                ],
                                [
                                # INGLES
                                    "lenguage_id" =>  "2",
                                    "name"        =>  "<p>Suitable for 18 to 50 years old</p>",
                                    "description" =>  '<p>Suitable for <span style="color: #366895;">people between 18 and 50 years ONLY.</span> No exceptions.</p>'
                                ],
                                [
                                # PORTUGUÉS
                                    "lenguage_id" =>  "3",
                                    "name"        =>  "<p>Adequado para 18 a 50 anos</p>",
                                    "description" =>  '<p>Somente apto para <span style="color: #366895;">pessoas entre 18 e 50 anos.</span> Sem exceções.</p>'
                                ]
                            ]
                        ],
                    #$complexity
                        [
                            "icon" =>  '$complex',
                            "order" =>  "6",
                            "translables" =>  [
                                [
                                #ESPAÑOL
                                    "lenguage_id" =>  "1",
                                    "name"        =>  "<p>Complejidad alta</p>",
                                    "description" =>  "<p>Complejidad ALTA. Para que tengas una excelente experiencia en el Glaciar debés tener la capacidad psicofísica suficiente para caminar al menos 7 horas y media, siendo parte del trayecto sobre el hielo con crampones.</p>"
                                ],
                                [
                                # INGLES
                                    "lenguage_id" =>  "2",
                                    "name"        =>  "<p>High complexity</p>",
                                    "description" =>  "<p>Complexity HIGH. In order to have a great experience on the Glacier, you should have the psychophysical capacity required to walk for at least 7 hours and a half, partly on ice and with crampons.</p>"
                                ],
                                [
                                # PORTUGUÉS
                                    "lenguage_id" =>  "3",
                                    "name"        =>  "<p>Alta complexidade</p>",
                                    "description" =>  "<p>Complexidade ALTA. Para que você tenha uma experiência excelente no Glaciar, é imprescindível contar com capacidade psicofísica suficiente para caminhar, pelos menos, 7 horas e meia, uma parte do percorrido sobre gelo e com grampos.</p>"
                                ]
                            ]
                        ],
                    #$does_not_include
                        [
                            "icon" =>  '$complexity',
                            "order" =>  "8",
                            "translables" =>  [
                                [
                                #ESPAÑOL
                                    "lenguage_id" =>  "1",
                                    "name"        =>  "<p>Items no incluidos</p>",
                                    "description" =>  "<p><strong>No incluye:</strong> Entrada al Parque Nacional | Comida y bebida | Ropa personal adecuada a las condiciones climáticas de la región. (frío, lluvia, viento, nieve)</p>"
                                ],
                                [
                                # INGLES
                                    "lenguage_id" =>  "2",
                                    "name"        =>  "<p>Items not included</p>",
                                    "description" =>  "<p><strong>Not included:</strong> Entrance to the National Park | Food and drink | Personal clothing appropriate to the climatic conditions of the region. (cold, rain, wind, snow)</p>"
                                ],
                                [
                                # PORTUGUÉS
                                    "lenguage_id" =>  "3",
                                    "name"        =>  "<p>Itens não inclusos</p>",
                                    "description" =>  "<p><strong>Não inclui:</strong> Entrada no Parque Nacional | Comida e bebida | Roupa pessoal adequada às condições climáticas da região. (frio, chuva, vento, neve)</p>"
                                ]
                            ]
                        ]
                ]
            ];

        //2 about
            $characteristics['characteristics'][] = [
                    "icon_id" => null,
                    "icon" => null,
                    "characteristic_type" =>  "about",
                    "order" => null,

                    "characteristics" => [],
                    "translables" => [
                        [
                            "lenguage_id" => 1,
                            "name" => "Sobre esta experiencia",
                            "description" => '<p>El Big Ice es una excursión de día completo que comienza con la búsqueda de los pasajeros en El Calafate. En nuestros confortables buses, camino al Parque Nacional Los Glaciares, los guías de turismo les brindarán información sobre la actividad, el lugar y el glaciar.</p>
                            <p style="text-align: justify;">&nbsp;</p>
                            <p><span style="color: #3686c3;"><strong>Una vez en el puerto “Bajo de las Sombras” (Ruta 11, a 70 km de El Calafate)</strong> <strong>embarcarán para cruzar el Lago Rico,</strong></span> llegando a la costa opuesta luego de aproximadamente 20 minutos de navegación frente a la imponente cara sur del Glaciar Perito Moreno.</p>
                            <p style="text-align: justify;">&nbsp;</p>
                            <p>Al llegar al refugio el grupo será recibido por expertos guías de montaña, quienes los dividirán en subgrupos y los acompañarán durante todo el recorrido. El trekking <span style="color: #3686c3;"><strong>comienza con una caminata por la morrena de aproximadamente 2 horas, </strong></span>donde se podrán observar diferentes vistas panorámicas del glaciar y del bosque.</p>
                            <p style="text-align: justify;">&nbsp;</p>
                            <p><strong><span style="color: #3686c3;">El Big Ice es una excursión altamente personalizada:</span>&nbsp; </strong>los grupos sobre el hielo serán de hasta 10 personas, acompañados por dos guías de montaña quienes les colocarán los&nbsp;crampones, cascos y arneses&nbsp;&nbsp; y les explicarán las&nbsp;normas básicas de seguridad.</p>
                            <p style="text-align: justify;">&nbsp;</p>
                            <p><span style="color: #3686c3;"><strong>La exigencia física es alta tanto en el bosque como sobre el hielo, donde la superficie es irregular pero firme y segura. </strong></span></p>
                            <p style="text-align: justify;">&nbsp;</p>
                            <p>Una vez en el glaciar y con los crampones puestos, el mundo toma una nueva perspectiva:&nbsp;lagunas azules, profundas grietas, enormes sumideros, mágicas cuevas, y la sensación única de sentirse en el corazón del glaciar.</p>
                            <p style="text-align: justify;">&nbsp;</p>
                            <p><span style="color: #3686c3;"><strong>Explorarán durante tres horas aproximadamente los rincones del glaciar más especial del mundo.</strong></span> Durante el recorrido, los guías de montaña los ayudarán a conocer mejor el hielo, su entorno y podrán dimensionar la&nbsp;magnitud del glaciar&nbsp;y disfrutar de la vista de las montañas aledañas, como los cerros Dos Picos, Pietrobelli y Cervantes. Además, contarán con media hora para almorzar y sorprenderse en un lugar de inigualable belleza.</p>
                            <p style="text-align: justify;">&nbsp;</p>
                            <p>Al finalizar la caminata sobre el glaciar, emprenderán el regreso por el mismo camino hasta llegar al Refugio, donde tendrán unos minutos para contemplar este lugar de inigualable belleza. Al tomar la embarcación de regreso, navegarán muy cerca de&nbsp;la cara sur del Glaciar Perito Moreno&nbsp;para luego volver a la “civilización”, ¡después de haber disfrutado <span style="color: #3686c3;"><strong>uno de los treks sobre hielo más espectaculares del mundo!</strong></span></p>
                            <p style="text-align: justify;">&nbsp;</p>
                            <p><strong>&nbsp;</strong><strong><span style="color: #3686c3;">La duración de la excursión con el traslado es de alrededor de doce horas en total</span>&nbsp;</strong>e incluye la visita guiada de una hora aproximadamente a las pasarelas del Glaciar Perito Moreno, a 7 km del puerto. Allí podrán disfrutar de la espectacular vista panorámica del glaciar y recorrer alguno de los senderos auto-guiados. En caso de no optar por nuestro transporte e ir por sus propios medios, el <span style="color: #3686c3;"><strong>Big Ice</strong></span> dura siete horas y media aproximadamente, saliendo desde el Puerto y regresando al mismo punto de partida.</p>
                            <p style="text-align: justify;">&nbsp;</p>
                            <p><span style="color: #3686c3;"><strong>El Big Ice se realiza en un ambiente natural por lo cual las condiciones climáticas y características del glaciar y sus alrededores cambian diariamente. Sin embargo, la excursión no se suspende, mientras que las condiciones de seguridad lo permitan. ¡Los esperamos!</strong></span></p>
                            <p style="text-align: justify;">&nbsp;</p>'
                        ],
                        [
                            "lenguage_id" => 2,
                            "name" => "About",
                            "description" => '<p style="text-align: justify;">Big Ice is a full day tour, starting with passenger pick-up in El Calafate. On our way to Parque Nacional Los Glaciares, aboard our comfortable buses, our tour guides will give you information on the tour, the place and the glacier.</p>
                            <p style="text-align: justify;">&nbsp;</p>
                            <p style="text-align: justify;"><span style="color: #2471b9;"><strong>Once you arrive at “Bajo de las Sombras” port (located on Route 11, 70 Km from El Calafate)</strong>&nbsp;</span><strong><span style="color: #2471b9;">you will board a ship to cross Lago Rico</span>,</strong>&nbsp;and descend on the opposite coast after a 20-minute navigation in front of the stunning south face of Glaciar Perito Moreno.</p>
                            <p style="text-align: justify;">&nbsp;</p>
                            <p style="text-align: justify;">When the group gets to the shelter, it will be welcomed by expert mountain guides, who will divide it in subgroups and will stay with them throughout the walk. The trekking&nbsp;<strong><span style="color: #2471b9;">starts with a walk along the moraine for about 2 hours</span>,&nbsp;</strong>where you will be able to enjoy different panoramic views of the glacier and the woods.</p>
                            <p style="text-align: justify;">&nbsp;</p>
                            <p style="text-align: justify;"><span style="color: #2471b9;"><strong>Big Ice is a highly personalized tour:</strong></span><strong>&nbsp;&nbsp;</strong>For the ice walk, passengers will be divided into groups of up to 10 people, with 2 mountain guides who will fit the crampons, helmets and harnesses and will explain the basic safety rules.</p>
                            <p style="text-align: justify;">&nbsp;</p>
                            <p style="text-align: justify;"><span style="color: #2471b9;"><strong>A high level of physical effort is required in the woods as well as on the ice, where the surface is irregular, but firm and safe.</strong></span></p>
                            <p style="text-align: justify;">&nbsp;</p>
                            <p style="text-align: justify;">Once you are on the glacier and with the crampons on, the world seems different: blue ponds, deep cracks, huge moulins, magical caves and the unique feeling of being in the heart of the glacier.</p>
                            <p style="text-align: justify;">&nbsp;</p>
                            <p style="text-align: justify;"><span style="color: #2471b9;"><strong>During approximately three hours, you will explore every corner of the most special glacier in the world.</strong></span>&nbsp;During the walk, the mountain guides will help you learn more about the ice and its environment and you will be able to appreciate the dimensions of the glacier and enjoy the view of the surrounding mountains, such as Dos Picos, Pietrobelli and Cervantes hills. There will be a 30-minute break to have lunch while enjoying the amazing and unique beauty of the surroundings.</p>
                            <p style="text-align: justify;">&nbsp;</p>
                            <p style="text-align: justify;">At the end of the trekking on the glacier, you will go back to the shelter by the same path, where you will have some minutes to appreciate this site of unparalleled beauty. Then, you will board the ship back and you will navigate very close to the south face of Glaciar Perito Moreno and later return to the “civilization”, after having enjoyed&nbsp;<span style="color: #2471b9;"><strong>one of the most spectacular ice treks in the world!</strong></span></p>
                            <p style="text-align: justify;">&nbsp;</p>
                            <p style="text-align: justify;"><span style="color: #2471b9;"><strong>The duration of this tour is about 12 hours, including the transfer </strong></span>and a one-hour guided visit to the walkways of Glaciar Perito Moreno, 7 km from the port. There, you will enjoy the spectacular panoramic view of the glacier and walk along some of the self-guided paths. If you don’t use our transfer and go by your own means, the <span style="color: #2471b9;"><strong>Big Ice</strong></span>&nbsp;tour takes about seven hours and a half, leaving from the port and returning to the same point.</p>
                            <p style="text-align: justify;">&nbsp;</p>
                            <p style="text-align: justify;"><span style="color: #2471b9;"><strong>The Big Ice tour is carried out in a natural environment, so weather conditions and the glacier and its surroundings change every day. This allows you to enjoy unique experiences at the most beautiful glacier in the world! We are waiting for you!</strong></span></p>
                            <p style="text-align: justify;">&nbsp;</p>'
                        ],
                        [
                            "lenguage_id" => 3,
                            "name" => "Sobre esta experiência",
                            "description" => '<p>O passeio começa no momento do <b style="color: #2471b9;">pick up</b>, cedo de manhã, no ponto de encontro acordado na cidade de El Calafate. Em nossos <b style="color: #2471b9;">confortáveis buses</b>, <b style="color: #2471b9;">um guia de turismo bilíngue</b> lhe oferecerá informações sobre a paisagem por descobrir.</p>
                            <p style="text-align: justify;">&nbsp;</p>
                            <p>Inclui visita guiada às <b style="color: #2471b9;">passarelas do Parque Nacional Los Glaciares</b>. Lá, você poderá desfrutar da espetacular paisagem panorâmica da geleira e percorrer algumas das trilhas autoguiadas.</p>
                            <p style="text-align: justify;">&nbsp;</p>
                            <p>Ao chegar o <b style="color: #2471b9;">porto “Bajo de las Sombras”</b>, localizado a apenas 7 km da geleira, você cruzará o Braço Rico em uma embarcação, para descer, depois de 20 minutos de navegação, no lado oposto.</p>
                            <p style="text-align: justify;">&nbsp;</p>
                            <p>Pequenos grupos de até <b style="color: #2471b9;">10 pessoas</b> são organizados para a caminhada, que começa pela morena sul da geleira. Em pouco mais de uma hora, chegam a um <b style="color: #2471b9;">ponto de observação espetacular</b> a partir do qual terão acesso ao gelo. Lá, os guias explicarão as normas básicas de segurança e ajustarão os <b style="color: #2471b9;">grampos, arreios e capacetes</b> necessários para iniciar a viagem.</p>
                            <p style="text-align: justify;">&nbsp;</p>
                            <p>Ao chegar à geleira, e com os grampos colocados, o mundo adquire uma nova perspectiva: <b style="color: #2471b9;">lagoas azuis, profundas gretas, enormes sumidouros,</b> mágicas cavernas e a sensação única de estar no coração da geleira.</p>
                            <p style="text-align: justify;">&nbsp;</p>
                            <p>Você sempre será acompanhado por nossos guias de montanha que, junto com você, explorarão por aproximadamente <b style="color: #2471b9;">três horas e meia</b> os cantos da geleira mais especial do mundo. No percorrido, com a ajuda dos guias, os grupos poderão conhecer melhor o gelo, seu entorno, assim como experimentar <b style="color: #2471b9;">a grandeza da geleira</b> e aproveitar da vista das montanhas ao redor, como o Cerro Dos Picos, o Cerro Pietrobelli e o Cerro Cervantes.&nbsp; Além disso, poderão desfrutar de meia hora para almoçar sobre o manto branco e se surpreender em um lugar de beleza incomparável.</p>
                            <p style="text-align: justify;">&nbsp;</p>
                            <p>Ao retornar à morena, os grupos caminharão mais uma hora até chegar ao barco de retorno, e navegarão muito próximo da <b style="color: #2471b9;">parede sul da Geleira Perito Moreno</b>. Os grupos retornarão à “civilização” depois de ter desfrutado de um dos passeios sobre gelo mais espetaculares do mundo!</p>
                            <p style="text-align: justify;">&nbsp;</p>
                            <p>O Big Ice é uma excursão de um dia completo que começa com a retirada dos passageiros na cidade de El Calafate. Em nossos confortáveis ônibus, caminho ao Parque Nacional Los Glaciares, os guias de turismo oferecerão informações sobre a atividade, a área e a geleira.</p>
                            <p style="text-align: justify;">&nbsp;</p>
                            <p><b style="color: #2471b9;">Ao chegar ao porto “Bajo de las Sombras” (Ruta 11, a 70 km de El Calafate), começa a navegação em barco, atravessando o Lago Rico</b> até atingir a costa oposta, logo após 20 minutos de navegação com vista para a parede sul do Glaciar Perito Moreno.</p>
                            <p style="text-align: justify;">&nbsp;</p>
                            <p>Ao chegar ao abrigo, o grupo será recebido por expertos guias de montanha que o dividirão em subgrupos e os acompanharão durante todo o percorrido. <b style="color: #2471b9;">O trekking começa com uma caminhada de aproximadamente 2 horas pela morena.</b> Lá, os passageiros poderão desfrutar de diferentes vistas panorâmicas da geleira e do bosque.</p>
                            <p style="text-align: justify;">&nbsp;</p>
                            <p><b style="color: #2471b9;">O Big Ice é uma excursão muito personalizada:</b>  Os grupos para caminhar sobre o gelo terão até 10 pessoas e serão acompanhadas por dois guias de montanha que colocarão os grampos, capacetes e arneses, e explicarão as normas básicas de segurança.</p>
                            <p style="text-align: justify;">&nbsp;</p>
                            <p><b style="color: #2471b9;">A exigência física é alta, tanto no bosque quanto sobre o gelo, onde a superfície é irregular, mas firme e segura.</b><br>
                            Ao chegar à geleira, e com os grampos colocados, o mundo adquire uma nova perspectiva: lagoas azuis, profundas fendas, enormes sumidouros, mágicas cavernas e a sensação única de estar no coração da geleira.</p>
                            <p style="text-align: justify;">&nbsp;</p>
                            <p><b style="color: #2471b9;">Os grupos explorarão e percorrerão, durante perto de três horas, a geleira mais bonita do mundo.</b> No percorrido, com a ajuda dos guias de montanha, os grupos poderão conhecer melhor o gelo, seu entorno, assim como experimentar a grandeza da geleira e desfrutar da vista das montanhas ao redor, como o cerro Dos Picos, o Cerro Pietrobelli e o Cerro Cervantes. Além disso, poderão desfrutar de meia hora para almoçar, e admirar um lugar de beleza incomparável.</p>
                            <p style="text-align: justify;">&nbsp;</p>
                            <p>Ao concluir a caminhada sobre a geleira, os grupos retornarão pelo mesmo caminho até o abrigo, onde terão alguns minutos para contemplar a beleza inigualável da área. Ao retornar à embarcação, os grupos navegarão muito próximo da parede sul do Glaciar Perito Moreno para retornar à “civilização” depois de ter desfrutado <b style="color: #2471b9;">um dos treks sobre gelo mais espetaculares do mundo!</b></p>
                            <p style="text-align: justify;">&nbsp;</p>
                            <p><b style="color: #2471b9;">A duração total da excursão mais o traslado é de aproximadamente doze horas</b> e inclui uma visita guiada de perto de uma hora às passarelas do Glaciar Perito Moreno, a 7 km do porto. Ali poderão desfrutar da espetacular vista panorâmica da geleira e percorrer algumas das trilhas autoguiadas. Se você não escolher nosso transporte e utilizar seus próprios meios, lembre-se que a duração do <b style="color: #2471b9;">Big Ice</b> é sete horas e meia aproximadamente, saindo do Porto e voltando para o mesmo ponto de saída.</p>
                            <p style="text-align: justify;">&nbsp;</p>
                            <p><b style="color: #2471b9;">O Big Ice é realizado em um ambiente natural e com condições climáticas e características da geleira e seu entorno que mudam todos os dias. Isso nos permite desfrutar de experiências irrepetíveis na geleira mais bonita do mundo! Esperamos vocês!</b></p>
                            <p style="text-align: justify;">&nbsp;</p>'
                        ]
                    ]
            ];

        //3 before_buying
            $characteristics['characteristics'][] = [
                "icon_id" => null,
                "characteristic_type" => "before_buying",
                "order" => null,

                "characteristics" => [
                    [
                        "icon" => null,
                        "order" => null,
                        "characteristics" => [
                            [
                                "icon_id" => null,
                                "icon" => '$obesity',
                                "order" => null,

                                "characteristics" => [],
                                "translables" => [
                                    [
                                        "lenguage_id" => 1,
                                        "name" => null,
                                        "description" => "<li><strong>Personas sedentarias con obesidad.</strong> No podrán participar personas con obesidad. En el caso de los adultos, la Organización Mundial de la Salud (OMS) define que la obesidad es tal, cuando una persona presenta un Índice de Masa Corporal (IMC) igual o superior a 30. El IMC se calcula dividiendo el peso de una persona en kilos por el cuadrado de su talla en metros: (kg/m2). Ante cualquier duda o consulta, envíanos una mail a clientes@hieloyaventura.com</li>",
                                    ],
                                    [
                                        "lenguage_id" => 2,
                                        "name" => null,
                                        "description" => '<li><strong>Obese persons.</strong> People with obesity may not participate. In the case of adults, the World Health Organization (WHO) defines obesity as when a person has a Body Mass Index (BMI) equal to or greater than 30. The BMI is calculated by dividing the weight of a person in kilos times the square of their height in meters: (kg/m2). If you have any questions or queries, send us an email to clientes@hieloyaventura.com</li>',
                                    ],
                                    [
                                        "lenguage_id" => 3,
                                        "name" => null,
                                        "description" => '<li><strong>Pessoas obesas.</strong> Pessoas com obesidade não podem participar. No caso dos adultos, a Organização Mundial de Saúde (OMS) define a obesidade como quando uma pessoa tem um Índice de Massa Corporal (IMC) igual ou superior a 30. O IMC é calculado dividindo o peso de uma pessoa em quilos vezes o quadrado de sua altura em metros: (kg/m2). Se tiver alguma dúvida ou questão, envie-nos um email para clientes@hieloyaventura.com</li>'
                                    ]
                                ]
                            ],
                            [
                                "icon_id" => null,
                                "order" => null,
                                "icon" => '$pregnant',

                                "characteristics" => [],
                                "translables" => [
                                    [
                                        "lenguage_id" => 1,
                                        "name" => null,
                                        "description" => "<li>Embarazadas</li>",
                                    ],
                                    [
                                        "lenguage_id" => 2,
                                        "name" => null,
                                        "description" => "<li>Pregnant</li>",
                                    ],
                                    [
                                        "lenguage_id" => 3,
                                        "name" => null,
                                        "description" => "<li>Grávidas</li>",
                                    ]
                                ]
                            ],
                            [
                                "icon_id" => null,
                                "order" => null,
                                "icon" => '$wheel_chair',

                                "characteristics" => [],
                                "translables" => [
                                    [
                                        "lenguage_id" => 1,
                                        "name" => null,
                                        "description" => "<li>Personas con cualquier grado o tipo de discapacidad física o mental que afecte su atención, marcha y/o coordinación.</li>",
                                    ],
                                    [
                                        "lenguage_id" => 2,
                                        "name" => null,
                                        "description" => "<li>People with any degree of physical or mental disability that affects their attention, ability to walk and/or coordination.</li>",
                                    ],
                                    [
                                        "lenguage_id" => 3,
                                        "name" => null,
                                        "description" => "<li>Pessoas com qualquer grau ou tipo de deficiência física ou mental que possa afetar sua atenção, marcha e/ou coordenação.</li>",
                                    ]
                                ]
                            ],
                            [
                                "icon_id" => null,
                                "order" => null,
                                "icon" => '$age_yellow',
                                "translables" =>  [
                                    [
                                    #ESPAÑOL
                                        "lenguage_id" =>  "1",
                                        "name"        =>  "<p>Apto para 18 a 50 años</p>",
                                        "description" =>  '<p>Solo apto para <span style="color: #366895;">personas de 18 a 50 años.</span> Sin excepción.</p>'
                                    ],
                                    [
                                    # INGLES
                                        "lenguage_id" =>  "2",
                                        "name"        =>  "<p>Suitable for 18 to 50 years old</p>",
                                        "description" =>  '<p>Suitable for <span style="color: #366895;">people between 18 and 50 years ONLY.</span> No exceptions.</p>'
                                    ],
                                    [
                                    # PORTUGUÉS
                                        "lenguage_id" =>  "3",
                                        "name"        =>  "<p>Adequado para 18 a 50 anos</p>",
                                        "description" =>  '<p>Somente apto para <span style="color: #366895;">pessoas entre 18 e 50 anos.</span> Sem exceções.</p>'
                                    ]
                                ]
                            ],
                            [
                                "icon_id" => null,
                                "order" => null,
                                "icon" => '$heart_rate',

                                "characteristics" => [],
                                "translables" => [
                                    [
                                        "lenguage_id" => 1,
                                        "name" => null,
                                        "description" => "<li>Personas con antecedentes cardíacos. Personas que sufran enfermedades cardiovasculares centrales o periféricas, que sus capacidades cardíacas o vasculares se encuentren disminuidas, o utilicen stent, bypass, marcapasos u otras prótesis. Ejemplo: medicamentos anticoagulantes, varices grado III (las que se evidencian gruesas y múltiples).</li>",
                                    ],
                                    [
                                        "lenguage_id" => 2,
                                        "name" => null,
                                        "description" => "<li>People with a cardiac history. Persons who suffer from central or peripheral heart or vascular disease, whose heart or capabilities are limited, or people with stents, bypass, pacemakers or other prosthesis. Example: anticoagulant medication, stage 3 varicose veins (multiple thick varicose veins that can noticed).</li>",
                                    ],
                                    [
                                        "lenguage_id" => 3,
                                        "name" => null,
                                        "description" => "<li>Pessoas com histórico de doença cardíaca. Pessoas com doenças cardiovasculares centrais ou periféricas, com capacidades cardíacas ou vasculares deficientes, ou quando utilizem stent, bypass, marca-passos ou outro tipo de prótese. Exemplo: medicamentos anti-coagulantes, varizes grau III (são grossas e múltiplas).Pessoas com doenças cardiovasculares centrais ou periféricas, com capacidades cardíacas ou vasculares deficientes, ou quando utilizem stent, bypass, marca-passos ou outro tipo de prótese. Exemplo: medicamentos anti-coagulantes, varizes grau III (são grossas e múltiplas).</li>",
                                    ]
                                ]
                            ],
                            [
                                "icon_id" => null,
                                "order" => null,
                                "icon" => '$lung',

                                "characteristics" => [],
                                "translables" => [
                                    [
                                        "lenguage_id" => 1,
                                        "name" => null,
                                        "description" => "<li>Personas que padezcan enfermedades provocadas POR discapacidades respiratorias como EPOC, asma, enfisema, entre otras.</li>",
                                    ],
                                    [
                                        "lenguage_id" => 2,
                                        "name" => null,
                                        "description" => "<li>People who suffer from diseases caused by respiratory disabilities such as COPD, asthma, emphysema, among others.</li>",
                                    ],
                                    [
                                        "lenguage_id" => 3,
                                        "name" => null,
                                        "description" => "<li>Pessoas que sofrem de doenças causadas por deficiências respiratórias como DPOC, asma, enfisema, entre outras.</li>",
                                    ]
                                ]
                            ]
                        ],
                        "translables" => []
                    ],
                    // [ //esta característica no está en BIG ICE, pero si tiene otras. Tengo que preguntar por eso
                    //     "icon_id" => null,
                    //     "order" => null,
                    //     "icon" => null,

                    //     "characteristics" => [],
                    //     "translables" => [
                    //         [
                    //             "lenguage_id" => 1,
                    //             "name" => null,
                    //             "description" => "<p>Los niños deben tener la capacidad psicofísica suficiente de para caminar 3 horas, de las cuales 1 hora y media es sobre el hielo con crampones.</p>"
                    //         ]
                    //     ]
                    // ]
                ],
                "translables" => [
                    [
                        "lenguage_id" => 1,
                        "name" => "A TENER EN CUENTA ANTES DE COMPRAR",
                        "description" => "<p><strong>Debido al grado de esfuerzo y dificultad (ALTA, con pronunciadas subidas y bajadas en un terreno irregular) que esta actividad presenta y con el solo objetivo de preservar la salud, no podrán participar:</strong></p>"
                    ],
                    [
                        "lenguage_id" => 2,
                        "name" => "BEFORE PURCHASING YOUR TICKETS, PLEASE KEEP IN MIND THE FOLLOWING:",
                        "description" => "<p><strong>Due to the effort and difficulty levels (HIGH, with steep and uneven ascents and descents) of this activity, and in order to preserve their health, the following persons cannot take the tour:</strong></p>"
                    ],
                    [
                        "lenguage_id" => 3,
                        "name" => "LEVAR EM CONTA ANTES DE COMPRAR",
                        "description" => "<p><strong>Devido ao nível de esforço e dificuldade da atividade (ALTA, com subidas e descidas pronunciadas e irregulares), e visando a proteger sua saúde, as pessoas a seguir não podem participar da excursão:</strong></p>"
                    ]
                ]
            ];

        //5 itinerary
            $characteristics['characteristics'][] = [
                "icon_id" => null,
                "characteristic_type" => 'itinerary',
                "order" => null,

                "characteristics" => [
                    [
                        "icon_id" => null,
                        "order" => null,
                        "icon" => null,
                        "characteristics" => [
                            [
                                "icon_id" => null,
                                "order" => null,
                                "icon" => '$itinerary_point',
                                "characteristics" => [],
                                "translables" => [
                                    [
                                        "lenguage_id" => 1,
                                        "name" => "Salida de El Calafate",
                                        "description" => "80km"
                                    ],
                                    [
                                        "lenguage_id" => 2,
                                        "name" => "Departure from El Calafate",
                                        "description" => "80km"
                                    ],
                                    [
                                        "lenguage_id" => 3,
                                        "name" => "Partida de El Calafate",
                                        "description" => "80km"
                                    ]
                                ]
                            ],
                            [
                                "icon_id" => null,
                                "order" => null,
                                "icon" => '$stairs',
                                "characteristics" => [],
                                "translables" => [
                                    [
                                        "lenguage_id" => 1,
                                        "name" => "Visita a Pasarelas",
                                        "description" => "1 hora"
                                    ],
                                    [
                                        "lenguage_id" => 2,
                                        "name" => "Visit to walkways",
                                        "description" => "1 hour"
                                    ],
                                    [
                                        "lenguage_id" => 3,
                                        "name" => "Visita a passarelas",
                                        "description" => "1 hora"
                                    ]
                                ]
                            ],
                            [
                                "icon_id" => null,
                                "order" => null,
                                "icon" => '$itinerary_ship',
                                "characteristics" => [],
                                "translables" => [
                                    [
                                        "lenguage_id" => 1,
                                        "name" => "Embarque en Puerto",
                                        "description" => "20min de navegación"
                                    ],
                                    [
                                        "lenguage_id" => 2,
                                        "name" => "Boarding in port",
                                        "description" => "20min of navigation"
                                    ],
                                    [
                                        "lenguage_id" => 3,
                                        "name" => "Embarque no porto",
                                        "description" => "20min de navegação"
                                    ]
                                ]
                            ],
                            [
                                "icon_id" => null,
                                "order" => null,
                                "icon" => '$itinerary_shoe',
                                "characteristics" => [],
                                "translables" => [
                                    [
                                        "lenguage_id" => 1,
                                        "name" => "Trekking en bosque",
                                        "description" => "2 horas"
                                    ],
                                    [
                                        "lenguage_id" => 2,
                                        "name" => "Trekking in forest",
                                        "description" => "2 hours"
                                    ],
                                    [
                                        "lenguage_id" => 3,
                                        "name" => "Trekking na floresta",
                                        "description" => "2 horas"
                                    ]
                                ]
                            ],
                            [
                                "icon_id" => null,
                                "order" => null,
                                "icon" => '$itinerary_shoe',
                                "characteristics" => [],
                                "translables" => [
                                    [
                                        "lenguage_id" => 1,
                                        "name" => "Trekking sobre Hielo",
                                        "description" => "3 horas"
                                    ],
                                    [
                                        "lenguage_id" => 2,
                                        "name" => "Ice Trekking",
                                        "description" => "3 hours"
                                    ],
                                    [
                                        "lenguage_id" => 3,
                                        "name" => "Trekking no gelo",
                                        "description" => "3 horas"
                                    ]
                                ]
                            ],
                            [
                                "icon_id" => null,
                                "order" => null,
                                "icon" => '$itinerary_ship',
                                "characteristics" => [],
                                "translables" => [
                                    [
                                        "lenguage_id" => 1,
                                        "name" => "Regreso al Puerto",
                                        "description" => "Caminata y navegación"
                                    ],
                                    [
                                        "lenguage_id" => 2,
                                        "name" => "Return to port",
                                        "description" => "Walk and navigation"
                                    ],
                                    [
                                        "lenguage_id" => 3,
                                        "name" => "Retornar ao porto",
                                        "description" => "Caminhada e navegação"
                                    ]
                                ]
                            ],
                            [
                                "icon_id" => null,
                                "order" => null,
                                "icon" => '$itinerary_point',
                                "characteristics" => [],
                                "translables" => [
                                    [
                                        "lenguage_id" => 1,
                                        "name" => "Regreso a El Calafate",
                                        "description" => "80km"
                                    ],
                                    [
                                        "lenguage_id" => 2,
                                        "name" => "Return to El Calafate",
                                        "description" => "80km"
                                    ],
                                    [
                                        "lenguage_id" => 3,
                                        "name" => "Voltar para El Calafate",
                                        "description" => "80km"
                                    ]
                                ]
                            ]
                        ],
                        "translables" => []
                    ],
                    [
                        "icon_id" => null,
                        "order" => null,
                        "icon" => null,
                        "characteristics" => [
                            [
                                "icon_id" => null,
                                "order" => null,
                                "icon" => null,
                                "characteristics" => [],
                                "translables" => [
                                    [
                                        "lenguage_id" => 1,
                                        "name" => "OPCIONAL CON TRASLADO",
                                        "description" => null
                                    ],
                                    [
                                        "lenguage_id" => 2,
                                        "name" => "OPTIONAL WITH TRANSFER",
                                        "description" => null
                                    ],
                                    [
                                        "lenguage_id" => 3,
                                        "name" => "OPCIONAL COM TRANSFER",
                                        "description" => null
                                    ]
                                ]
                            ],
                            [
                                "icon_id" => null,
                                "order" => null,
                                "icon" => null,
                                "characteristics" => [],
                                "translables" => [
                                    [
                                        "lenguage_id" => 1,
                                        "name" => "TOUR INCLUIDO",
                                        "description" => null
                                    ],
                                    [
                                        "lenguage_id" => 2,
                                        "name" => "TOUR INCLUDED",
                                        "description" => null
                                    ],
                                    [
                                        "lenguage_id" => 3,
                                        "name" => "PASSEIO INCLUÍDO",
                                        "description" => null
                                    ]
                                ]
                            ]
                        ],
                        "translables" => []
                    ]
                ],
                "translables" => [
                    [
                        "lenguage_id" => 1,
                        "name" => "Itinerario Big Ice",
                        "description" => null
                    ],
                    [
                        "lenguage_id" => 2,
                        "name" => "Big Ice itinerary",
                        "description" => null
                    ],
                    [
                        "lenguage_id" => 3,
                        "name" => "Itinerário Big Ice",
                        "description" => null
                    ]
                ]
            ];

        //7 carry
            $characteristics['characteristics'][] =
                [
                    "icon_id" => null,
                    "characteristic_type" => "carry",
                    "order" => null,
                    "icon" => null,
                    "characteristics" => [
                        [
                            "icon_id" => null,
                            "order" => null,
                            "icon" => '$cloth',
                            "characteristics" => [],
                            "translables" => [
                                [
                                    "lenguage_id" => 1,
                                    "name" => null,
                                    "description" => "<p>Vestir ropa cómoda y abrigada. Campera y pantalón impermeable, calzado deportivo o botas de trekking impermeables. El clima es cambiante y hay que estar preparado para no mojarse ni pasar frío. Lentes de sol, protector solar, guantes, gorro.</p>"
                                ],
                                [
                                    "lenguage_id" => 2,
                                    "name" => null,
                                    "description" => "<p>Wear comfortable and warm clothes. A rain jacket, long waterproof trousers, trekking boots, a medium-size backpack (40Lts), sunglasses, sunscreen, gloves, a wool hat.</p>"
                                ],
                                [
                                    "lenguage_id" => 3,
                                    "name" => null,
                                    "description" => "<p>Vestir roupa confort&aacute;vel e quente. Casaco imperme&aacute;vel, cal&ccedil;as cumpridas e imperme&aacute;veis, botasde trekking imperme&aacute;veis, mochila m&eacute;dia (40 litros), &oacute;culosde sol, protetor solar, luvas e gorro</p>"
                                ]
                            ]
                        ],
                        [
                            "icon_id" => null,
                            "order" => null,
                            "icon" => '$food',
                            "characteristics" => [],
                            "translables" => [
                                [
                                    "lenguage_id" => 1,
                                    "name" => null,
                                    "description" => "<p>Llevar comida y bebida para el día. La Empresa no cuenta con servicio de venta de comidas ni bebidas.</p>"
                                ],
                                [
                                    "lenguage_id" => 2,
                                    "name" => null,
                                    "description" => "<p>Bring food and drink for the day. The company does not sell food and drinks.</p>"
                                ],
                                [
                                    "lenguage_id" => 3,
                                    "name" => null,
                                    "description" => "<p>Levar comida e bebida para todo o dia. A empresa n&atilde;o oferece servi&ccedil;o de venda de comidas nem bebidas.</p>"
                                ]
                            ]
                        ],
                        [
                            "icon_id" => null,
                            "characteristic_type" => null,
                            "order" => null,
                            "icon" => '$ticket',
                            "characteristics" => [],
                            "translables" => [
                                [
                                    "lenguage_id" => 1,
                                    "name" => null,
                                    "description" => "<p>Deberás presentar tu entrada al Parque Nacional. Podés comprarla <span class='text-primary'>acá (Seleccionar: 'Acceso Corredor Rio Mitre y Glaciar Perito Moreno')</span> o abonarla en efectivo (en pesos argentinos) al llegar al Parque Nacional.</p>"
                                ],
                                [
                                    "lenguage_id" => 2,
                                    "name" => null,
                                    "description" => '<p>Tickets must be exhibited at the entrance of the Parque Nacional. You can buy your ticket here&nbsp;<span style="color: #2471B9;"><a style="color: #2471B9;" href="https://ventaweb.apn.gob.ar/reserva/inicio?dp=05" target="_blank" rel="noopener"><strong>(Select: &ldquo;Acceso Corredor Rio Mitre y Glaciar Perito Moreno&rdquo;)</strong></a></span>&nbsp;or pay it in cash (in Argentine pesos) when you arrive at the Parque Nacional.</p>'
                                ],
                                [
                                    "lenguage_id" => 3,
                                    "name" => null,
                                    "description" => '<p>Voc&ecirc; dever&aacute; apresentar seu ingresso ao Parque Nacional. Pode comprar o ingresso aqui&nbsp;<span style="color: #2471B9;"><a style="color: #2471B9;" href="https://ventaweb.apn.gob.ar/reserva/inicio?dp=05" target="_blank" rel="noopener"><strong>(Selecionar: &ldquo;Acceso Corredor Rio Mitre y Glaciar Perito Moreno&rdquo;)</strong></a></span>&nbsp;ou pagar com dinheiro (pesos argentinos) ao chegar ao Parque Nacional.</p>'
                                ]
                            ]
                        ]
                    ],
                    "translables" => [
                        [
                            "lenguage_id" => 1,
                            "name" => "Que llevar en la excursión?",
                            "description" => null
                        ],
                        [
                            "lenguage_id" => 2,
                            "name" => "What to bring?",
                            "description" => null
                        ],
                        [
                            "lenguage_id" => 3,
                            "name" => "O que É PRECISO levar?",
                            "description" => null
                        ]
                    ]
                ];
        //9 restrictions
            $characteristics['characteristics'][] = [
                "icon_id" => null,
                "characteristic_type" => "restrictions",
                "order" => null,
                "icon" => null,

                "characteristics" => [],
                "translables" => [
                    [
                        "lenguage_id" => 1,
                        "name" => "Restricciones importantes antes de comprar",
                        "description" => '<p>Debido al grado de esfuerzo y dificultad que esta actividad presenta y con el solo objetivo de preservar la salud, no podrán participar de la excursión ciertas personas.</p>
                        <p style="text-align: justify;">&nbsp;</p>'
                    ],
                    [
                        "lenguage_id" => 2,
                        "name" => "Important restrictions before buying",
                        "description" => '<p>Due to the degree of effort and difficulty that this activity presents and with the sole objective of preserving health, people with:</p>
                        <p style="text-align: justify;">&nbsp;</p>'
                    ],
                    [
                        "lenguage_id" => 3,
                        "name" => "Restrições importantes antes de comprar",
                        "description" => '<p>Devido ao grau de esforço e dificuldade que esta atividade apresenta e com o único objetivo de preservar a saúde, as pessoas com:</p>
                        <p style="text-align: justify;">&nbsp;</p>'
                    ]
                ]
            ];

        //10 comparison_sail_perito
            $characteristics['characteristics'][] = [
                "icon_id" => null,
                "characteristic_type" => "comparison_sail_perito",
                "order" => null,
                "icon" => null,

                "characteristics" => [],
                "translables" => [
                    [
                        "lenguage_id" => "1",
                        "name" => "Navega frente al Glaciar Perito Moreno",
                        "description" => "1"
                    ],
                    [
                        "lenguage_id" => "2",
                        "name" => "Sail in front of the Perito Moreno Glacier",
                        "description" => "1"
                    ],
                    [
                        "lenguage_id" => "3",
                        "name" => "Navegue em frente ao Glaciar Perito Moreno",
                        "description" => "1"
                    ]
                ]
            ];
        //11 comparison_trekking_ice
            $characteristics['characteristics'][] = [
                "icon_id" => null,
                "characteristic_type" => "comparison_trekking_ice",
                "order" => null,
                "icon" => null,

                "characteristics" => [],
                "translables" => [
                    [
                        "lenguage_id" => "1",
                        "name" => "Trekking sobre hielo",
                        "description" => "3 horas"
                    ],
                    [
                        "lenguage_id" => "2",
                        "name" => "Ice trekking",
                        "description" => "3 hours"
                    ],
                    [
                        "lenguage_id" => "3",
                        "name" => "Trekking no gelo",
                        "description" => "3 horas"
                    ]
                ]
            ];
        //12 comparison_dificult
            $characteristics['characteristics'][] = [
                "icon_id" => null,
                "characteristic_type" => "comparison_dificult",
                "order" => null,
                "icon" => null,

                "characteristics" => [],
                "translables" => [
                    [
                        "lenguage_id" => "1",
                        "name" => "Dificultad",
                        "description" => "Alta"
                    ],
                    [
                        "lenguage_id" => "2",
                        "name" => "Difficulty",
                        "description" => "Low"
                    ],
                    [
                        "lenguage_id" => "3",
                        "name" => "Dificuldade",
                        "description" => "Baixa"
                    ]
                ]
            ];
        //14 comparison_fissures
            $characteristics['characteristics'][] = [
                "icon_id" => null,
                "characteristic_type" => "comparison_fissures",
                "order" => null,
                "icon" => null,

                "characteristics" => [],
                "translables" => [
                    [
                        "lenguage_id" => "1",
                        "name" => "Vista de grietas",
                        "description" => "1"
                    ],
                    [
                        "lenguage_id" => "2",
                        "name" => "View of cracks",
                        "description" => "1"
                    ],
                    [
                        "lenguage_id" => "3",
                        "name" => "Visão de rachaduras",
                        "description" => "1"
                    ]
                ]
            ];
        //15 comparison_seracs
            $characteristics['characteristics'][] = [
                "icon_id" => null,
                "characteristic_type" => "comparison_seracs",
                "order" => null,
                "icon" => null,

                "characteristics" => [],
                "translables" => [
                    [
                        "lenguage_id" => "1",
                        "name" => "Vista de Seracs",
                        "description" => "1"
                    ],
                    [
                        "lenguage_id" => "2",
                        "name" => "View of Seracs",
                        "description" => "1"
                    ],
                    [
                        "lenguage_id" => "3",
                        "name" => "Visão de Seracs",
                        "description" => "1"
                    ]
                ]
            ];
        //16 comparison_sinks
            $characteristics['characteristics'][] = [
                "icon_id" => null,
                "characteristic_type" => "comparison_sinks",
                "order" => null,
                "icon" => null,

                "characteristics" => [],
                "translables" => [
                    [
                        "lenguage_id" => "1",
                        "name" => "Vista de sumideros",
                        "description" => "1"
                    ],
                    [
                        "lenguage_id" => "2",
                        "name" => "View of sinkholes",
                        "description" => "1"
                    ],
                    [
                        "lenguage_id" => "3",
                        "name" => "Visão dos sumidouros",
                        "description" => "1"
                    ]
                ]
            ];
        //17 comparison_caves
            $characteristics['characteristics'][] = [
                "icon_id" => null,
                "characteristic_type" => "comparison_caves",
                "order" => null,
                "icon" => null,

                "characteristics" => [],
                "translables" => [
                    [
                        "lenguage_id" => "1",
                        "name" => "Vista de cuevas",
                        "description" => "eventualmente"
                    ],
                    [
                        "lenguage_id" => "2",
                        "name" => "View of caves",
                        "description" => "eventually"
                    ],
                    [
                        "lenguage_id" => "3",
                        "name" => "Vista das cavernas",
                        "description" => "eventualmente"
                    ]
                ]
            ];
        //18 comparison_laggons
            $characteristics['characteristics'][] = [
                "icon_id" => null,
                "characteristic_type" => "comparison_laggons",
                "order" => null,
                "icon" => null,
                "characteristics" => [],
                "translables" => [
                    [
                        "lenguage_id" => "1",
                        "name" => "Vista de lagunas",
                        "description" => "1"
                    ],
                    [
                        "lenguage_id" => "2",
                        "name" => "View of lagoons",
                        "description" => "1"
                    ],
                    [
                        "lenguage_id" => "3",
                        "name" => "Vista das lagoas",
                        "description" => "1"
                    ]
                ]
            ];
        //19 comparison_group_size
            $characteristics['characteristics'][] = [
                "icon_id" => null,
                "characteristic_type" => "comparison_group_size",
                "order" => null,
                "icon" => null,
                "characteristics" => [],
                "translables" => [
                    [
                        "lenguage_id" => "1",
                        "name" => "Tamaño de grupo",
                        "description" => "10 sobre el hielo"
                    ],
                    [
                        "lenguage_id" => "2",
                        "name" => "Group size",
                        "description" => "10 on ice"
                    ],
                    [
                        "lenguage_id" => "3",
                        "name" => "Tamanho do grupo",
                        "description" => "10 no gelo"
                    ]
                ]
            ];
        //20 comparison_lagoon_coast_trekking
            $characteristics['characteristics'][] = [
                "icon_id" => null,
                "characteristic_type" => "comparison_lagoon_coast_trekking",
                "order" => null,
                "icon" => null,
                "characteristics" => [],
                "translables" => [
                    [
                        "lenguage_id" => "1",
                        "name" => "Trekking por costa del lago",
                        "description" => "0"
                    ],
                    [
                        "lenguage_id" => "2",
                        "name" => "Trekking along the lake coast",
                        "description" => "0"
                    ],
                    [
                        "lenguage_id" => "3",
                        "name" => "Trekking ao longo da costa do lago",
                        "description" => "0"
                    ]
                ]
            ];
        //21 comparison_forest_trekking
            $characteristics['characteristics'][] = [
                "icon_id" => null,
                "characteristic_type" => "comparison_forest_trekking",
                "order" => null,
                "icon" => null,
                "characteristics" => [],
                "translables" => [
                    [
                        "lenguage_id" => "1",
                        "name" => "Trekking por bosque",
                        "description" => "1"
                    ],
                    [
                        "lenguage_id" => "2",
                        "name" => "Trekking through forest",
                        "description" => "1"
                    ],
                    [
                        "lenguage_id" => "3",
                        "name" => "Trekking pela floresta",
                        "description" => "1"
                    ]
                ]
            ];
        //22 comparison_food_included
            $characteristics['characteristics'][] = [
                "icon_id" => null,
                "characteristic_type" => "comparison_food_included",
                "order" => null,
                "icon" => null,
                "characteristics" => [],
                "translables" => [
                    [
                        "lenguage_id" => "1",
                        "name" => "Comida incluida",
                        "description" => "0"
                    ],
                    [
                        "lenguage_id" => "2",
                        "name" => "Lunch included",
                        "description" => "0"
                    ],
                    [
                        "lenguage_id" => "3",
                        "name" => "Almoço incluso",
                        "description" => "0"
                    ]
                ]
            ];
        //23 comparison_hotel_transfer
            $characteristics['characteristics'][] = [
                "icon_id" => null,
                "characteristic_type" => "comparison_hotel_transfer",
                "order" => null,
                "icon" => null,
                "characteristics" => [],
                "translables" => [
                    [
                        "lenguage_id" => "1",
                        "name" => "Traslado desde el hotel",
                        "description" => "optativo"
                    ],
                    [
                        "lenguage_id" => "2",
                        "name" => "Transfer from the hotel",
                        "description" => "optional"
                    ],
                    [
                        "lenguage_id" => "3",
                        "name" => "Traslado do hotel",
                        "description" => "opcional"
                    ]
                ]
            ];
        //25 comparison_current_price
            $characteristics['characteristics'][] = [
                "icon_id" => null,
                "characteristic_type" => "comparison_current_price",
                "order" => null,
                "icon" => null,
                "characteristics" => [],
                "translables" => [
                    [
                        "lenguage_id" => "1",
                        "name" => "Precio actual",
                        "description" => 80000
                    ],
                    [
                        "lenguage_id" => "2",
                        "name" => "Actual Price",
                        "description" => 80000
                    ],
                    [
                        "lenguage_id" => "3",
                        "name" => "Preço real",
                        "description" => 80000
                    ]
                ]
            ];
        //26 purchase_detail
            $characteristics['characteristics'][] = [
                "icon_id" => null,
                "characteristic_type" => 'purchase_detail',
                "order" => null,

                "characteristics" => [
                    [
                        "icon_id" => null,
                        "order" => null,
                        "icon" => null,
                        "characteristics" => [
                            [
                                "icon_id" => null,
                                "order" => null,
                                "icon" => '$check',
                                "con_trf" => null,
                                "characteristics" => [],
                                "translables" => [
                                    [
                                        "lenguage_id" => 1,
                                        "name" => "Guía de Turismo bilingüe (español e inglés) durante el recorrido en el glaciar",
                                        "description" => null
                                    ],
                                    [
                                        "lenguage_id" => 2,
                                        "name" => "Bilingual tourist guide (Spanish and English) during the glacier tour",
                                        "description" => null
                                    ],
                                    [
                                        "lenguage_id" => 3,
                                        "name" => "Guia turístico bilíngue (espanhol e inglês) durante o passeio pela geleira",
                                        "description" => null
                                    ]
                                ]
                            ],
                            // [
                            //     "icon_id" => null,
                            //     "order" => null,
                            //     "icon" => '$check',
                            //     "con_trf" => null,
                            //     "characteristics" => [],
                            //     "translables" => [
                            //         [
                            //             "lenguage_id" => 1,
                            //             "name" => "Guía español e inglés durante el recorrido en el glaciar",
                            //             "description" => ""
                            //         ],
                            //         [
                            //             "lenguage_id" => 2,
                            //             "name" => "Spanish and English guide during the tour on the glacier",
                            //             "description" => ""
                            //         ],
                            //         [
                            //             "lenguage_id" => 3,
                            //             "name" => "Guia espanhol e inglês durante o passeio pela geleira",
                            //             "description" => ""
                            //         ]
                            //     ]
                            // ],
                            // [
                            //     "icon_id" => null,
                            //     "order" => null,
                            //     "icon" => '$check',
                            //     "con_trf" => null,
                            //     "characteristics" => [],
                            //     "translables" => [
                            //         [
                            //             "lenguage_id" => 1,
                            //             "name" => "Visita de 1 hora aproximadamente a las pasarelas",
                            //             "description" => null
                            //         ],
                            //         [
                            //             "lenguage_id" => 2,
                            //             "name" => "Visit of approximately 1 hour to the catwalks",
                            //             "description" => null
                            //         ],
                            //         [
                            //             "lenguage_id" => 3,
                            //             "name" => "Visita de aproximadamente 1 hora às passarelas",
                            //             "description" => null
                            //         ]
                            //     ]
                            // ],
                            [
                                "icon_id" => null,
                                "order" => null,
                                "icon" => '$check',
                                "con_trf" => null,
                                "characteristics" => [],
                                "translables" => [
                                    [
                                        "lenguage_id" => 1,
                                        "name" => "Navegación frente a la pared sur del glaciar",
                                        "description" => null
                                    ],
                                    [
                                        "lenguage_id" => 2,
                                        "name" => "Navigation in front of the southern wall of the glacier",
                                        "description" => null
                                    ],
                                    [
                                        "lenguage_id" => 3,
                                        "name" => "Navegação em frente à parede sul da geleira",
                                        "description" => null
                                    ]
                                ]
                            ],
                            [
                                "icon_id" => null,
                                "order" => null,
                                "icon" => '$check',
                                "con_trf" => null,
                                "characteristics" => [],
                                "translables" => [
                                    [
                                        "lenguage_id" => 1,
                                        "name" => "Caminata por costa del lago hasta llegar al glaciar",
                                        "description" => null
                                    ],
                                    [
                                        "lenguage_id" => 2,
                                        "name" => "Walk along the coast of the lake until you reach the glacier",
                                        "description" => null
                                    ],
                                    [
                                        "lenguage_id" => 3,
                                        "name" => "Caminhe ao longo da costa do lago até chegar à geleira",
                                        "description" => null
                                    ]
                                ]
                            ],
                            [
                                "icon_id" => null,
                                "order" => null,
                                "icon" => '$check',
                                "con_trf" => null,
                                "characteristics" => [],
                                "translables" => [
                                    [
                                        "lenguage_id" => 1,
                                        "name" => "Caminata por bosque con Vía Ferrata (4hs total)",
                                        "description" => null
                                    ],
                                    [
                                        "lenguage_id" => 2,
                                        "name" => "Forest walk with Via Ferrata (4 hours total)",
                                        "description" => null
                                    ],
                                    [
                                        "lenguage_id" => 3,
                                        "name" => "Caminhada na floresta com a Via Ferrata (4 horas no total)",
                                        "description" => null
                                    ]
                                ]
                            ],
                            [
                                "icon_id" => null,
                                "order" => null,
                                "icon" => '$check',
                                "con_trf" => null,
                                "characteristics" => [],
                                "translables" => [
                                    [
                                        "lenguage_id" => 1,
                                        "name" => "Trekking sobre hielo de aproximadamente 3 horas",
                                        "description" => null
                                    ],
                                    [
                                        "lenguage_id" => 2,
                                        "name" => "Trekking on ice of approximately 3 hours",
                                        "description" => null
                                    ],
                                    [
                                        "lenguage_id" => 3,
                                        "name" => "Trekking no gelo de aproximadamente 3 horas",
                                        "description" => null
                                    ]
                                ]
                            ],
                            [
                                "icon_id" => null,
                                "order" => null,
                                "icon" => '$check',
                                "con_trf" => null,
                                "characteristics" => [],
                                "translables" => [
                                    [
                                        "lenguage_id" => 1,
                                        "name" => "Acceso a nuestro refugio de montaña",
                                        "description" => null
                                    ],
                                    [
                                        "lenguage_id" => 2,
                                        "name" => "Access to our mountain refuge",
                                        "description" => null
                                    ],
                                    [
                                        "lenguage_id" => 3,
                                        "name" => "Acesso ao nosso refúgio de montanha",
                                        "description" => null
                                    ]
                                ]
                            ],
                            [
                                "icon_id" => null,
                                "order" => null,
                                "icon" => '$check',
                                "con_trf" => 1,
                                "characteristics" => [],
                                "translables" => [
                                    [
                                        "lenguage_id" => 1,
                                        "name" => "CON TRASLADO: Inicio en su hotel de El Calafate",
                                        "description" => null
                                    ],
                                    [
                                        "lenguage_id" => 2,
                                        "name" => "WITH TRANSFER: Start at your hotel in El Calafate",
                                        "description" => null
                                    ],
                                    [
                                        "lenguage_id" => 3,
                                        "name" => "COM TRANSFER: Comece no seu hotel em El Calafate",
                                        "description" => null
                                    ]
                                ]
                            ],
                            [
                                "icon_id" => null,
                                "order" => null,
                                "icon" => '$check',
                                "con_trf" => 1,
                                "characteristics" => [],
                                "translables" => [
                                    [
                                        "lenguage_id" => 1,
                                        "name" => "CON TRASLADO: Traslado con guía y visita de aproximadamente 1 hora a pasarelas",
                                        "description" => null
                                    ],
                                    [
                                        "lenguage_id" => 2,
                                        "name" => "WITH TRANSFER: Optional transfer with guide, including a visit of about one hour to the walkways",
                                        "description" => null
                                    ],
                                    [
                                        "lenguage_id" => 3,
                                        "name" => "COM TRANSFER: Traslado opcional, com guia e visita de aproximadamente uma hora às passarelas",
                                        "description" => null
                                    ]
                                ]
                            ],
                        ],
                        "translables" => []
                    ]
                ],
                "translables" => [
                    [
                        "lenguage_id" => 1,
                        "name" => "Detalle de compra",
                        "description" => ""
                    ],
                    [
                        "lenguage_id" => 2,
                        "name" => "Purchase detail",
                        "description" => ""
                    ],
                    [
                        "lenguage_id" => 3,
                        "name" => "Detalhe da compra",
                        "description" => ""
                    ]
                ]
            ];
        //27 comparison_ratio
            $characteristics['characteristics'][] = [
                "icon_id" => null,
                "characteristic_type" => "comparison_ratio",
                "order" => null,
                "icon" => null,
                "characteristics" => [],
                "translables" => [
                    [
                        "lenguage_id" => "1",
                        "name" => "RATIO",
                        "description" => "Altamente personalizado! 1 guía cada 5 personas"
                    ],
                    [
                        "lenguage_id" => "2",
                        "name" => "RATIO",
                        "description" => "Highly personalized! 1 guide every 5 people"
                    ],
                    [
                        "lenguage_id" => "3",
                        "name" => "RATIO",
                        "description" => "Altamente personalizado! 1 guia a cada 5 pessoas"
                    ]
                ]
            ];
        // 28 comparison_total_walk
        $characteristics['characteristics'][] = [
            "icon_id" => null,
            "characteristic_type" => "comparison_total_walk",
            "order" => null,
            "icon" => null,
            "characteristics" => [],
            "translables" => [
                [
                    "lenguage_id" => "1",
                    "name" => "Caminata total",
                    "description" => "12 km aprox"
                ],
                [
                    "lenguage_id" => "2",
                    "name" => "Caminata total",
                    "description" => "12 km aprox"
                ],
                [
                    "lenguage_id" => "3",
                    "name" => "Caminata total",
                    "description" => "12 km aprox"
                ]
            ]
        ];
        // 29 comparison_waterfall_view
            $characteristics['characteristics'][] = [
                "icon_id" => null,
                "characteristic_type" => "comparison_waterfall_view",
                "order" => null,
                "icon" => null,
                "characteristics" => [],
                "translables" => [
                    [
                        "lenguage_id" => "1",
                        "name" => "Visita a Cascada",
                        "description" => "1"
                    ],
                    [
                        "lenguage_id" => "2",
                        "name" => "Visita a Cascada",
                        "description" => "1"
                    ],
                    [
                        "lenguage_id" => "3",
                        "name" => "Visita a Cascada",
                        "description" => "1"
                    ]
                ]
            ];
        return $characteristics;
    }

    public function safariAzul()
    {
        $characteristics = [];

        //1 characteristics
            $characteristics['characteristics'][] = [
                # Generales"1"
                "icon_id" =>  NULL,
                "icon" =>  NULL,
                "characteristic_type" =>  "characteristics",
                "order" =>  NULL,
                #

                # translables
                    "translables" => [
                        [
                            "lenguage_id" =>  1,
                            "name" =>  "Características de la actividad",
                            "description" =>  NULL
                        ],
                        [
                            "lenguage_id" =>  2,
                            "name" =>  "Activity characteristic",
                            "description" =>  NULL
                        ],
                        [
                            "lenguage_id" =>  3,
                            "name" =>  "Característica da atividade",
                            "description" =>  NULL
                        ]
                    ],
                #

                # Las 6 características o ḿas
                #Translables
                "characteristics" =>
                [
                    #$clockConTraslado
                        [
                            "icon" =>  '$clock',
                            "order" =>  "1",
                            "translables" =>  [
                                [
                                    #ESPAÑOL
                                    "lenguage_id" =>  "1",
                                    "name"        =>  "<p>9 horas (todo el dia)</p>",
                                    "description" =>  '<p>La duración CON traslado y pasarelas es de aproximadamente 9 horas (Día completo). SIN traslado y pasarelas es de aproximadamente 2.45 horas</p>'
                                ],
                                [
                                    # INGLES
                                    "lenguage_id" =>  "2",
                                    "name"        =>  "<p>9 hours (Full day)</p>",  
                                    "description" =>  "<p>The duration WITH transfer and footbridges is approximately 9 hours (Full day). WITHOUT transfer and walkways is approximately 2.45 hours</p>"
                                ],
                                [
                                    # PORTUGUÉS
                                    "lenguage_id" =>  "3",
                                    "name"        =>  "<p>9 horas (Dia completo)</p>",  
                                    "description" =>  "<p>A duração COM traslado e passarelas é de aproximadamente 9 horas (dia inteiro). SEM transfer e passarelas é de aproximadamente 2,45 horas</p>"
                                ]
                            ]
                        ],
                    #$clockSinTraslado
                        // [
                        //     "icon" =>  '$clock',
                        //     "order" =>  "1",
                        //     "translables" =>  [
                        //         [
                        //             #ESPAÑOL
                        //             "lenguage_id" =>  "1",
                        //             "name"        =>  "Duración SIN traslado y pasarelas",
                        //             "description" =>  '<p>Aproximadamente 2.45 horas</p>'
                        //         ],
                        //         [
                        //             # INGLES
                        //             "lenguage_id" =>  "2",
                        //             "name"        =>  "Duration WITHOUT transfer and walkways",
                        //             "description" =>  "<p>Approximately 12 hours (Full day)</p>"
                        //         ],
                        //         [
                        //             # PORTUGUÉS
                        //             "lenguage_id" =>  "3",
                        //             "name"        =>  "Duração SEM traslado e passarelas",
                        //             "description" =>  "<p>Aproximadamente 12 horas (Dia inteiro)</p>"
                        //         ]
                        //     ]
                        // ],
                    #$calendar
                        [
                            "icon" =>  '$calendar',
                            "order" =>  "2",
                            "translables" =>  [
                                [
                                #ESPAÑOL
                                    "lenguage_id" =>  "1",
                                    "name"        =>  "<p>15 julio a 31 mayo</p>",
                                    "description" =>  "<p>La disponibilidad de esta excursión es del 15 de Julio al 31 de Mayo</p>"
                                ],
                                [
                                # INGLES
                                    "lenguage_id" =>  "2",
                                    "name"        =>  "<p>15 july to 31 may</p>",
                                    "description" =>  "<p>The availability of this excursion is from July 15 to May 31</p>"
                                ],
                                [
                                # PORTUGUÉS
                                    "lenguage_id" =>  "3",
                                    "name"        =>  "<p>15 de julho a 31 de maio</p>",
                                    "description" =>  "<p>A disponibilidade desta excursão é de 15 de julho a 31 de maio</p>"
                                ]
                            ]
                        ],
                    #$bus
                        [
                            "icon" =>  '$bus',
                            "order" =>  "3",
                            "translables" =>  [
                                [
                                    #ESPAÑOL
                                        "lenguage_id" =>  "1",
                                        "name"        =>  '<p>Traslado opcional</p>',
                                        "description" =>  '<p>Opcional traslado con guía y visita de aproximadamente dos horas a pasarelas.</p>'
                                ],
                                [
                                    # INGLES
                                        "lenguage_id" =>  "2",
                                        "name"        =>  '<p>Optional transfer</p>',
                                        "description" =>  "<p>Optional transfer with guide and visit of approximately two hours to catwalks.</p>"
                                ],
                                [
                                    # PORTUGUÉS
                                        "lenguage_id" =>  "3",
                                        "name"        =>  '<p>Transferência opcional</p>',
                                        "description" =>  "<p>Traslado opcional com guia e visita de aproximadamente duas horas às passarelas.</p>"
                                ]
                            ]
                        ],
                    #$guide
                        [
                            "icon" =>  '$guide',
                            "order" =>  "4",
                            "translables" =>  [
                                [
                                #ESPAÑOL
                                    "lenguage_id" =>  "1",
                                    "name"        =>  "<p>Guías español e inglés.</p>",
                                    "description" =>  "<p>Nuestros guías hablan español e inglés.</p>"
                                ],
                                [
                                # INGLES
                                    "lenguage_id" =>  "2",
                                    "name"        =>  "<p>Spanish and English guides.</p>",
                                    "description" =>  "<p>Our guides speak Spanish and English.</p>"
                                ],
                                [
                                # PORTUGUÉS
                                    "lenguage_id" =>  "3",
                                    "name"        =>  "<p>Guias em espanhol e inglês</p>",
                                    "description" =>  "<p>Nossos guias falam espanhol e inglês.</p>"
                                ]
                            ]
                        ],
                    #$age
                        [
                            "icon" =>  '$age',
                            "order" =>  "5",
                            "translables" =>  [
                                [
                                #ESPAÑOL
                                    "lenguage_id" =>  "1",
                                    "name"        =>  "<p>Apto para 6 a 70 años</p>",
                                    "description" =>  '<p>Solo apto para <span style="color: #366895;">personas de 6 a 70 años.</span> Sin excepción.</p>'
                                ],
                                [
                                # INGLES
                                    "lenguage_id" =>  "2",
                                    "name"        =>  "<p>Suitable for 6 to 70 years old</p>",
                                    "description" =>  '<p>Suitable for <span style="color: #366895;">people between 6 and 70 years ONLY.</span> No exceptions.</p>'
                                ],
                                [
                                # PORTUGUÉS
                                    "lenguage_id" =>  "3",
                                    "name"        =>  "<p>Adequado para 6 a 70 anos</p>",
                                    "description" =>  '<p>Somente apto para <span style="color: #366895;">pessoas entre 6 e 70 anos.</span> Sem exceções.</p>'
                                ]
                            ]
                        ],
                    #$complexity
                        [
                            "icon" =>  '$complex',
                            "order" =>  "6",
                            "translables" =>  [
                                [
                                #ESPAÑOL
                                    "lenguage_id" =>  "1",
                                    "name"        =>  "<p>Complejidad baja</p>",
                                    "description" =>  "<p>Si bien la intensidad es baja el terreno presenta piedras, pendientes suaves y escaleras.</p>"
                                ],
                                [
                                # INGLES
                                    "lenguage_id" =>  "2",
                                    "name"        =>  "<p>Low complexity</p>",
                                    "description" =>  "<p>Even though the difficulty of the tour is low, the surface has stones, gentle slopes and stairs.</p>"
                                ],
                                [
                                # PORTUGUÉS
                                    "lenguage_id" =>  "3",
                                    "name"        =>  "<p>Baixa complexidade</p>",
                                    "description" =>  "<p>Embora a dificuldade seja baixa, o terreno tem pedras, declives e escadas.</p>"
                                ]
                            ]
                        ],
                    #$does_not_include
                        [
                            "icon" =>  '$complexity',
                            "order" =>  "7",
                            "translables" =>  [
                                [
                                #ESPAÑOL
                                    "lenguage_id" =>  "1",
                                    "name"        =>  "<p>Items no incluidos</p>",
                                    "description" =>  "<p><strong>No incluye:</strong> Entrada al Parque Nacional | Comida y bebida | Ropa personal adecuada a las condiciones climáticas de la región. (frío, lluvia, viento, nieve)</p>"
                                ],
                                [
                                # INGLES
                                    "lenguage_id" =>  "2",
                                    "name"        =>  "<p>Items not included</p>",
                                    "description" =>  "<p><strong>Not included:</strong> Entrance to the National Park | Food and drink | Personal clothing appropriate to the climatic conditions of the region. (cold, rain, wind, snow)</p>"
                                ],
                                [
                                # PORTUGUÉS
                                    "lenguage_id" =>  "3",
                                    "name"        =>  "<p>Itens não inclusos</p>",
                                    "description" =>  "<p><strong>Não inclui:</strong> Entrada no Parque Nacional | Comida e bebida | Roupa pessoal adequada às condições climáticas da região. (frio, chuva, vento, neve)</p>"
                                ]
                            ]
                        ]
                ]
            ];

        //2 about
            $characteristics['characteristics'][] = [
                    "icon_id" => null,
                    "icon" => null,
                    "characteristic_type" =>  "about",
                    "order" => null,

                    "characteristics" => [],
                    "translables" => [
                        [
                            "lenguage_id" => 1,
                            "name" => "Sobre esta experiencia",
                            "description" => '<p><span style="color: #3686c3;"><strong>El Safari Azul</strong></span> está pensado para aquellos que, además de navegar frente al Glaciar Perito Moreno, <span style="color: #3686c3;"><strong>sueñan con acercarse al hielo glaciar!</strong></span></p>
                            <p style="text-align: justify;">&nbsp;</p>
                            <p>La excursión comienza en El Calafate cuando el bus parte con destino al <span style="color: #3686c3;"><strong>Parque Nacional Los Glaciares.</strong></span> Una vez en el Puerto Bajo de las Sombras, a solo 7 km de las pasarelas, tomaremos un barco para cruzar el Lago Rico y, luego de navegar 20 minutos, desembarcaremos en la costa opuesta.</p>
                            <p style="text-align: justify;">&nbsp;</p>
                            <p>Poco a poco <span style="color: #3686c3;"><strong>caminaremos por 30 minutos</strong></span> siempre con vista a la pared sur del Glaciar por si nos sorprende algún estruendoso desprendimiento. <span style="color: #3686c3;"><strong>Una vez al lado del hielo será tiempo de una experiencia inolvidable…</strong></span> ¡Podremos disfrutar plenamente de sus intensos y variados azules, blancos y sus caprichosas formas.</p>
                            <p style="text-align: justify;">&nbsp;</p>
                            <p>Tendremos tiempo para tomar muchas fotos y luego regresaremos al lugar de embarque siempre acompañados por un guía experimentado.<strong><span style="color: #3686c3;">La caminata total es de 1.30hs</span></strong>aproximadamente por un terreno natural de arena y piedras con alguna pendientes y escaleras. El recorrido, de un kilometro y medio, será por la costa del lago y por un frondoso bosque con vista al Glaciar.</p>
                            <p style="text-align: justify;">&nbsp;</p>
                            <p>Finalmente <span style="color: #3686c3;"><strong>tomaremos el barco para apreciar desde el agua</strong></span> y, a pocos metros de distancia, toda la cara sur del glaciar y poder ver cada detalle de la marmolada pared helada.</p>
                            <p style="text-align: justify;">&nbsp;</p>
                            <p>Una vez en el puerto, en caso de haber contratado el servicio de transfer con Hielo y Aventura, <strong><span style="color: #3686c3;">tomaremos el bus hacia las pasarelas</span></strong> donde tendremos 2 horas para disfrutar la increíble vista panorámica. En caso de haberse trasladado por sus propios medios, podrá optar libremente por el tiempo de visita en las pasarelas. Además, podrán aprovechar este tiempo para consumir la vianda que deberán traer desde El Calafate.</p>
                            <p style="text-align: justify;">&nbsp;</p>
                            <p>Llegaremos a El Calafate luego de recorrer la estepa patagónica, con el alma cargada de la energía natural de este glaciar único. <span style="color: #3686c3;"><strong>¡Estamos ansiosos por ser sus anfitriones!</strong></span></p>
                            <p style="text-align: justify;">&nbsp;</p>
                            <p><span style="color: #3686c3;"><strong>El Safari Azul se realiza en un ambiente natural por lo cual las condiciones climáticas y características del glaciar y sus alrededores cambian diariamente. Sin embargo, la excursión no se suspende, mientras que las condiciones de seguridad lo permitan. ¡Los esperamos!<strong></span></p>
                            <p style="text-align: justify;">&nbsp;</p>'
                        ],
                        [
                            "lenguage_id" => 2,
                            "name" => "About",
                            "description" => '<p style="text-align: justify;">The <span style="color: #3686c3;"><strong>Safari Azul Tour</strong></span> has been thought for people willing to navigate in front of the Perito Moreno Glacier and <span style="color: #3686c3;"><strong>enjoy being very close to the ice.</strong></span></p>
                            <p style="text-align: justify;">&nbsp;</p>
                            <p style="text-align: justify;">The tour will start in the city of El Calafate when the bus departs towards <span style="color: #2471b9;"><strong>Parque Nacional Los Glaciares.</strong></span> Once you arrive at the “Bajo de las Sombras” port (at only 7 Km from the walkways), you’ll board a ship to cross Lago Rico and descend on the opposite coast after a 20-minute navigation.</p>
                            <p style="text-align: justify;">&nbsp;</p>
                            <p style="text-align: justify;">After walking for <strong><span style="color: #2471b9;">about 30 minutes,</span></strong> always with a view of the southern side of the glacier just in case there were thunderous ice calvings, and <span style="color: #2471b9;"><strong>after reaching the ice, you’ll have an unforgettable experience.</strong></span> At that point, you’ll also enjoy different and intense shades of blue and white colors, with capricious forms.</p>
                            <p style="text-align: justify;">&nbsp;</p>
                            <p style="text-align: justify;">Passengers will have enough time to take pictures and afterwards return to the embarking point accompanied by our experienced guide. You’ll enjoy an hour-and-a-half walk along a natural surface of sand and stones, with some slopes and stairs. You’ll walk about 1.5 kilometers by the cost of the lake and through a greenwood with a view to the Perito Moreno Glacier.</p>
                            <p style="text-align: justify;">&nbsp;</p>
                            <p style="text-align: justify;">Finally, <span style="color: #2471b9;"><strong> you’ll embark on the ship to enjoy, from the water and at only some meters away,</strong></span> every detail of the marbled side of the glacier.</p>
                            <p style="text-align: justify;">&nbsp;</p>
                            <p style="text-align: justify;">Upon arriving to the port, <span style="color: #2471b9;"><strong>you’ll take the bus towards the walkways</strong></span> where you’ll have two hours to enjoy an incredible panoramic view. You’ll also have time to have the lunch you brought from El Calafate.</p>
                            <p style="text-align: justify;">&nbsp;</p>
                            <p style="text-align: justify;">After visiting the Patagonian Steppe, you’ll arrive to El Calafate, with your heart full of the natural energy of this unique glacier. <span style="color: #2471b9;"><strong>We can’t wait to be your host!</strong></span></p>
                            <p style="text-align: justify;">&nbsp;</p>
                            <p style="text-align: justify;"><span style="color: #2471b9;"><strong>The Safari Azul is carried out in a natural environment, so weather conditions and the glacier and its surroundings change every day.</strong></span> However, <span style="color: #2471b9;"><strong>the excursion is not suspended,</strong></span> as long as security conditions allow it</p>
                            <p style="text-align: justify;">&nbsp;</p>'
                        ],
                        [
                            "lenguage_id" => 3,
                            "name" => "Sobre esta experiência",
                            "description" => '<p><b style="color: #2471b9;">O Safári Azul</b> foi organizado para quem quer navegar diante do Glaciar Perito Moreno e <b style="color: #2471b9;">sonha com estar bem perto da geleira.</b></p>
                            <p style="text-align: justify;">&nbsp;</p>
                            <p>A excursão começa na cidade de El Calafate com a saída do ônibus para o <b style="color: #2471b9;">Parque Nacional Los Glaciares.</b>Ao chegar ao Porto Bajo las Sombras, localizado a apenas 7 km das passarelas, cruzaremos o Lago Rico em uma embarcação, para descer, logo de 20 minutos de navegação, no lado oposto.</p>
                            <p style="text-align: justify;">&nbsp;</p>
                            <p>Vamos fazer <b style="color: #2471b9;">30 minutos de caminhada,</b> sempre olhando para a parede sul do Glaciar pois poderiam acontecer desprendimentos estrondosos e, <b style="color: #2471b9;">ao chegar ao gelo, teremos uma experiência inesquecível.</b> Ai, desfrutaremos de intensos e variados matizes de cor azul, branco e de formas caprichosas.</p>
                            <p style="text-align: justify;">&nbsp;</p>
                            <p>Teremos tempo de tirar muitas fotos e, logo após, voltaremos para o local de embarque, sempre em companhia de um guia experimentado. <b style="color: #2471b9;">O tempo total da caminhada é aprox. 1.30 horas</b> sobre um terreno natural de areia e pedras, com alguns declives e escadas. No percorrido de um quilômetro e meio bordejaremos o lago e atravessaremos uma floresta com árvores frondosas com vista à geleira.</p>
                            <p style="text-align: justify;">&nbsp;</p>
                            <p>Finalmente, <b style="color: #2471b9;">voltaremos para a embarcação para desfrutar, na água e a poucos metros de distância,</b> do lado sul da geleira e olhar cada detalhe da gelada parede marmorizada.</p>
                            <p style="text-align: justify;">&nbsp;</p>
                            <p>Ao chegar ao porto, <b style="color: #2471b9;">partiremos em ônibus para as passarelas</b> para desfrutar de duas horas de incríveis vistas panorâmicas. Os passageiros poderão aproveitar esse tempo para comer os lanches que eles deverão trazer da cidade de El Calafate.</p>
                            <p style="text-align: justify;">&nbsp;</p>
                            <p>Após termos percorrido a estepe patagônica, chegaremos a El Calafate com a alma carregada da energia natural dessa geleira única. <b style="color: #2471b9;"> Estamos ansiosos para ser seus anfitriões!</b></p>
                            <p style="text-align: justify;">&nbsp;</p>
                            <p><span style="color: #2471b9;">O Safari Azul é realizado em um ambiente natural e com condições climáticas e características da geleira e seu entorno que mudam todos os dias. No entanto, <strong> a excursão não está suspensa</strong>, desde que as condições de segurança o permitan.</p>
                            <p style="text-align: justify;">&nbsp;</p>'
                        ]
                    ]
            ];

        //3 before_buying 
            $characteristics['characteristics'][] = [
                "icon_id" => null,
                "characteristic_type" => "before_buying",
                "order" => null,
                "characteristics" => [
                    [
                        "icon" => null,
                        "order" => null,
                        "characteristics" => [
                            [
                                "icon_id" => null,
                                "order" => null,
                                "icon" => '$pregnant',

                                "characteristics" => [],
                                "translables" => [
                                    [
                                        "lenguage_id" => 1,
                                        "name" => null,
                                        "description" => "No apto para personas embarazadas. No apto para personas con dificultad de movilidad. No apto para personas con problemas cardiacos u otros que puedan perjudicar su salud durante la caminata.",
                                    ],
                                    [
                                        "lenguage_id" => 2,
                                        "name" => null,
                                        "description" => "Not suitable for pregnant women. Not suitable for people with mobility difficulties. Not suitable for people with heart problems or others that may harm their health during the walk.",
                                    ],
                                    [
                                        "lenguage_id" => 3,
                                        "name" => null,
                                        "description" => "Não indicado para grávidas. Não indicado para pessoas com dificuldades de locomoção. Não indicado para pessoas com problemas cardíacos ou outros que possam prejudicar a saúde durante a caminada.",
                                    ]
                                ]
                            ],
                            [
                                "icon_id" => null,
                                "icon" => '$obesity',
                                "order" => null,

                                "characteristics" => [],
                                "translables" => [
                                    [
                                        "lenguage_id" => 1,
                                        "name" => null,
                                        "description" => "<li><strong> Personas sedentarias con obesidad.</strong> No podrán participar personas con obesidad. En el caso de los adultos, la Organización Mundial de la Salud (OMS) define que la obesidad es tal, cuando una persona presenta un Índice de Masa Corporal (IMC) igual o superior a 30. El IMC se calcula dividiendo el peso de una persona en kilos por el cuadrado de su talla en metros: (kg/m2). Ante cualquier duda o consulta, envíanos una mail a clientes@hieloyaventura.com</li>",
                                    ],
                                    [
                                        "lenguage_id" => 2,
                                        "name" => null,
                                        "description" => '<li><strong>Obese persons.</strong> People with obesity may not participate. In the case of adults, the World Health Organization (WHO) defines obesity as when a person has a Body Mass Index (BMI) equal to or greater than 30. The BMI is calculated by dividing the weight of a person in kilos times the square of their height in meters: (kg/m2). If you have any questions or queries, send us an email to clientes@hieloyaventura.com</li>',
                                    ],
                                    [
                                        "lenguage_id" => 3,
                                        "name" => null,
                                        "description" => '<li><strong>Pessoas obesas.</strong> Pessoas com obesidade não podem participar. No caso dos adultos, a Organização Mundial de Saúde (OMS) define a obesidade como quando uma pessoa tem um Índice de Massa Corporal (IMC) igual ou superior a 30. O IMC é calculado dividindo o peso de uma pessoa em quilos vezes o quadrado de sua altura em metros: (kg/m2). Se tiver alguma dúvida ou questão, envie-nos um email para clientes@hieloyaventura.com</li>'
                                    ]
                                ]
                            ],
                            [
                                "icon_id" => null,
                                "icon" => '$age_yellow',
                                "order" => null,
                                "translables" =>  [
                                    [
                                    #ESPAÑOL
                                        "lenguage_id" =>  "1",
                                        "name"        =>  "<p>Apto para 6 a 70 años</p>",
                                        "description" =>  '<p>Solo apto para <span style="color: #366895;">personas de 6 a 70 años.</span> Sin excepción.</p>'
                                    ],
                                    [
                                    # INGLES
                                        "lenguage_id" =>  "2",
                                        "name"        =>  "<p>Suitable for 6 to 70 years old</p>",
                                        "description" =>  '<p>Suitable for <span style="color: #366895;">people between 6 and 70 years ONLY.</span> No exceptions.</p>'
                                    ],
                                    [
                                    # PORTUGUÉS
                                        "lenguage_id" =>  "3",
                                        "name"        =>  "<p>Adequado para 6 a 70 anos</p>",
                                        "description" =>  '<p>Somente apto para <span style="color: #366895;">pessoas entre 6 e 70 anos.</span> Sem exceções.</p>'
                                    ]
                                ]
                            ],
                        ],
                        "translables" => []
                    ],
                ],
                "translables" => [
                    [
                        "lenguage_id" => 1,
                        "name" => "A TENER EN CUENTA ANTES DE COMPRAR",
                        "description" => '<p><span style="color: rgb(36, 113, 185);"><strong>Aclaraci&oacute;n:</strong></span> Esta excursi&oacute;n NO incluye caminata sobre el Glaciar Perito Moreno.</p>
                        <p style="text-align: justify;">&nbsp;</p>
                        <p><strong><span style="color: rgb(36, 113, 185);">No incluye:</span></strong>&nbsp;Entrada al Parque Nacional | Comida y bebida | Ropa personal adecuada a las condiciones clim&aacute;ticas de la regi&oacute;n. (fr&iacute;o, lluvia, viento, nieve).</p>
                        <p style="text-align: justify;">&nbsp;</p>
                        <p>Debido al grado de esfuerzo y dificultad que esta actividad presenta y con el solo objetivo de preservar la salud, no podrán participar de la excursión ciertas personas.</p>
                        <p style="text-align: justify;">&nbsp;</p>'
                    ],
                    [
                        "lenguage_id" => 2,
                        "name" => "BEFORE PURCHASING YOUR TICKETS, PLEASE KEEP IN MIND THE FOLLOWING:",
                        "description" => '<p><span style="color: rgb(36, 113, 185);"><strong>Important:</strong></span>&nbsp;This tour doesn&rsquo;t include walking on the Perito Moreno Glacier.</p>
                        <p style="text-align: justify;">&nbsp;</p>
                        <p><strong><span style="color: rgb(36, 113, 185);">Not included:</span></strong>&nbsp;Ticket to the National Park | Food and drink. | Personal clothes suitable for the weather conditions of the place. (cold, rain, wind, snow) </p>
                        <p style="text-align: justify;">&nbsp;</p>
                        <p>Due to the degree of effort and difficulty that this activity presents and with the sole objective of preserving health, certain people will not be able to participate in the excursion</p>
                        <p style="text-align: justify;">&nbsp;</p>'
                    ],
                    [
                        "lenguage_id" => 3,
                        "name" => "LEVAR EM CONTA ANTES DE COMPRAR",
                        "description" => '<p><span style="color: rgb(36, 113, 185);"><strong>Esclarecimento:</strong></span>&nbsp; A excurs&atilde;o N&Atilde;O INCLUI caminhada sobre o Glaciar Perito Moreno.</p>
                        <p style="text-align: justify;">&nbsp;</p>
                        <p><span style="color: rgb(36, 113, 185);"><strong>N&atilde;o&nbsp;inclui:</strong></span>&nbsp; Ingresso ao Parque Nacional | Comida e bebida | Vestimenta pessoal adequada para as condi&ccedil;&otilde;es clim&aacute;ticas pr&oacute;prias da regi&atilde;o (frio, chuva, vento, neve).</p>
                        <p style="text-align: justify;">&nbsp;</p>
                        <p>Devido ao grau de esforço e dificuldade que esta atividade apresenta e com o único objetivo de preservar a saúde, certas pessoas ñao poderao participar da excursao</p>
                        <p style="text-align: justify;">&nbsp;</p>'
                    ]
                ]
            ];

        //5 itinerary
            $characteristics['characteristics'][] = [
                "icon_id" => null,
                "characteristic_type" => 'itinerary',
                "order" => null,

                "characteristics" => [ 
                    [
                        "icon_id" => null,
                        "order" => null,
                        "icon" => null,
                        "characteristics" => [
                            [
                                "icon_id" => null,
                                "order" => null,
                                "icon" => '$itinerary_point',
                                "characteristics" => [],
                                "translables" => [
                                    [
                                        "lenguage_id" => 1,
                                        "name" => "Salida de El Calafate",
                                        "description" => "70 Km al Glaciar"
                                    ],
                                    [
                                        "lenguage_id" => 2,
                                        "name" => "Departing from El Calafate",
                                        "description" => "70 km dto the glacier"
                                    ],
                                    [
                                        "lenguage_id" => 3,
                                        "name" => "Saída de El Calafate",
                                        "description" => "70 km até a geleira"
                                    ]
                                ]
                            ],
                            [
                                "icon_id" => null,
                                "order" => null,
                                "icon" => '$itinerary_ship',
                                "characteristics" => [],
                                "translables" => [
                                    [
                                        "lenguage_id" => 1,
                                        "name" => "Embarque en Puerto",
                                        "description" => "20min de navegación"
                                    ],
                                    [
                                        "lenguage_id" => 2,
                                        "name" => "Embarking at the Port",
                                        "description" => "20min navigation"
                                    ],
                                    [
                                        "lenguage_id" => 3,
                                        "name" => "Embarque no Porto",
                                        "description" => "20min de navegação"
                                    ]
                                ]
                            ],
                            [
                                "icon_id" => null,
                                "order" => null,
                                "icon" => '$itinerary_shoe',
                                "characteristics" => [],
                                "translables" => [
                                    [
                                        "lenguage_id" => 1,
                                        "name" => "Caminata por la costa hacia el glaciar",
                                        "description" => null
                                    ],
                                    [
                                        "lenguage_id" => 2,
                                        "name" => "Walking along the cosat and being close to the glacier",
                                        "description" => null
                                    ],
                                    [
                                        "lenguage_id" => 3,
                                        "name" => "Caminhada pela costa e aproximação da geleria",
                                        "description" => null
                                    ]
                                ]
                            ],
                            [
                                "icon_id" => null,
                                "order" => null,
                                "icon" => '$touch',
                                "characteristics" => [],
                                "translables" => [
                                    [
                                        "lenguage_id" => 1,
                                        "name" => "Tocamos el Glaciar",
                                        "description" => null
                                    ],
                                    [
                                        "lenguage_id" => 2,
                                        "name" => "We touch the Glacier",
                                        "description" => null
                                    ],
                                    [
                                        "lenguage_id" => 3,
                                        "name" => "Nós tocamos a geleira",
                                        "description" => null
                                    ]
                                ]
                            ],
                            [
                                "icon_id" => null,
                                "order" => null,
                                "icon" => '$walk',
                                "characteristics" => [],
                                "translables" => [
                                    [
                                        "lenguage_id" => 1,
                                        "name" => "Vistas Panorámicas y Breve Caminata por el bosque",
                                        "description" => null
                                    ],
                                    [
                                        "lenguage_id" => 2,
                                        "name" => "Panoramic Views and Brief Hike through the Forest",
                                        "description" => null
                                    ],
                                    [
                                        "lenguage_id" => 3,
                                        "name" => "Vistas Panorâmicas e Breve Caminhada pela Floresta",
                                        "description" => null
                                    ]
                                ]
                            ],
                            [
                                "icon_id" => null,
                                "order" => null,
                                "icon" => '$itinerary_ship',
                                "characteristics" => [],
                                "translables" => [
                                    [
                                        "lenguage_id" => 1,
                                        "name" => "Navegación de regreso al Puerto",
                                        "description" => null
                                    ],
                                    [
                                        "lenguage_id" => 2,
                                        "name" => "Navegating back to the Port",
                                        "description" => null
                                    ],
                                    [
                                        "lenguage_id" => 3,
                                        "name" => "Navegação de volta para o Porto",
                                        "description" => null
                                    ]
                                ]
                            ],
                            [
                                "icon_id" => null,
                                "order" => null,
                                "icon" => '$blue_stairs',
                                "characteristics" => [],
                                "translables" => [
                                    [
                                        "lenguage_id" => 1,
                                        "name" => "Visita a Pasarelas",
                                        "description" => null
                                    ],
                                    [
                                        "lenguage_id" => 2,
                                        "name" => "Visiting the walkways",
                                        "description" => null
                                    ],
                                    [
                                        "lenguage_id" => 3,
                                        "name" => "Visita às passarelas",
                                        "description" => null
                                    ]
                                ]
                            ],
                            [
                                "icon_id" => null,
                                "order" => null,
                                "icon" => '$itinerary_point',
                                "characteristics" => [],
                                "translables" => [
                                    [
                                        "lenguage_id" => 1,
                                        "name" => "Regreso a El Calafate",
                                        "description" => null
                                    ],
                                    [
                                        "lenguage_id" => 2,
                                        "name" => "Returning to the city of El Calafate",
                                        "description" => null
                                    ],
                                    [
                                        "lenguage_id" => 3,
                                        "name" => "Retorno a El Calafate",
                                        "description" => null
                                    ]
                                ]
                            ]
                        ],
                        "translables" => []
                    ],
                    [
                        "icon_id" => null,
                        "order" => null,
                        "icon" => null,
                        "characteristics" => [
                            [
                                "icon_id" => null,
                                "order" => null,
                                "icon" => null,
                                "characteristics" => [],
                                "translables" => [
                                    [
                                        "lenguage_id" => 1,
                                        "name" => "OPCIONAL CON TRASLADO",
                                        "description" => null
                                    ],
                                    [
                                        "lenguage_id" => 2,
                                        "name" => "OPTIONAL WITH TRANSFER",
                                        "description" => null
                                    ],
                                    [
                                        "lenguage_id" => 3,
                                        "name" => "OPCIONAL COM TRANSFER",
                                        "description" => null
                                    ]
                                ]
                            ],
                            [
                                "icon_id" => null,
                                "order" => null,
                                "icon" => null,
                                "characteristics" => [],
                                "translables" => [
                                    [
                                        "lenguage_id" => 1,
                                        "name" => "TOUR INCLUIDO",
                                        "description" => null
                                    ],
                                    [
                                        "lenguage_id" => 2,
                                        "name" => "TOUR INCLUDED",
                                        "description" => null
                                    ],
                                    [
                                        "lenguage_id" => 3,
                                        "name" => "PASSEIO INCLUÍDO",
                                        "description" => null
                                    ]
                                ]
                            ]
                        ],
                        "translables" => []
                    ]
                ],
                "translables" => [
                    [
                        "lenguage_id" => 1,
                        "name" => "Itinerario Safari Azul",
                        "description" => "Este itinerario es orientativo y puede variar el orden."
                    ],
                    [
                        "lenguage_id" => 2,
                        "name" => "Safari Azul Itineray",
                        "description" => "This itinerary is merely illustrative and the order of activities may change."
                    ],
                    [
                        "lenguage_id" => 3,
                        "name" => "Itinerário Safari Azul",
                        "description" => "Este itinerário é apenas orientativo e a ordem pode mudar."
                    ]
                ]
            ];

        //7 carry
            $characteristics['characteristics'][] =
                [
                    "icon_id" => null,
                    "characteristic_type" => "carry",
                    "order" => null,
                    "icon" => null,
                    "characteristics" => [
                        [
                            "icon_id" => null,
                            "order" => null,
                            "icon" => '$cloth',
                            "characteristics" => [],
                            "translables" => [
                                [
                                    "lenguage_id" => 1,
                                    "name" => null,
                                    "description" => "<p>Vestir ropa cómoda y abrigada. Campera y pantalón impermeable, calzado deportivo o botas de trekking impermeables. El clima es cambiante y hay que estar preparado para no mojarse ni pasar frío. Lentes de sol, protector solar, guantes, gorro.</p>"
                                ],
                                [
                                    "lenguage_id" => 2,
                                    "name" => null,
                                    "description" => "<p>Wear comfortable and warm clothes. A rain jacket, long waterproof trousers, trekking boots or waterproof sport shoes, The weather is changeable and you need to be prepared not to get wet or cold.  Sunglasses, sunscreen, gloves, a wool hat.</p>"
                                ],
                                [
                                    "lenguage_id" => 3,
                                    "name" => null,
                                    "description" => "<p>Vestir roupa confortável e quente. Casaco impermeável, calças cumpridas e impermeáveis, calçado esportivo botas de trekking impermeáveis. O tempo é variável e é preciso estar preparado para não se molhar ou ficar com frio. Óculos de sol, protetor solar, luvas e gorro.</p>"
                                ]
                            ]
                        ],
                        [
                            "icon_id" => null,
                            "order" => null,
                            "icon" => '$food',
                            "characteristics" => [],
                            "translables" => [
                                [
                                    "lenguage_id" => 1,
                                    "name" => null,
                                    "description" => "<p>Llevar comida y bebida para el día. La Empresa no cuenta con servicio de venta de comidas ni bebidas.</p>"
                                ],
                                [
                                    "lenguage_id" => 2,
                                    "name" => null,
                                    "description" => "<p>Bring food and drink for the day. The company does not sell food and drinks.</p>"
                                ],
                                [
                                    "lenguage_id" => 3,
                                    "name" => null,
                                    "description" => "<p>Levar comida e bebida para todo o dia. A empresa não oferece serviço de venda de comidas nem bebidas.</p>"
                                ]
                            ]
                        ],
                        [
                            "icon_id" => null,
                            "characteristic_type" => null,
                            "order" => null,
                            "icon" => '$ticket',
                            "characteristics" => [],
                            "translables" => [
                                [
                                    "lenguage_id" => 1,
                                    "name" => null,
                                    "description" => '<p>Deber&aacute;s presentar tu entrada al Parque Nacional. Pod&eacute;s comprarla&nbsp;<span style="color: rgb(36, 113, 185);"><a style="color: rgb(36, 113, 185);" href="https://ventaweb.apn.gob.ar/reserva/inicio?dp=05" target="_blank" rel="noopener"><strong>ac&aacute; (Seleccionar: &ldquo;Acceso Corredor Rio Mitre y Glaciar Perito Moreno&rdquo;)</strong></a>&nbsp;</span>o abonarla en efectivo (en pesos argentinos) al llegar al Parque Nacional.</p>'
                                ],
                                [
                                    "lenguage_id" => 2,
                                    "name" => null,
                                    "description" => '<p>Tickets must be exhibited at the entrance of the Parque Nacional. You can buy your ticket here&nbsp;<span style="color: rgb(36, 113, 185);"><a style="color: rgb(36, 113, 185);" href="https://ventaweb.apn.gob.ar/reserva/inicio?dp=05" target="_blank" rel="noopener"><strong>(Select: &ldquo;Acceso Corredor Rio Mitre y Glaciar Perito Moreno&rdquo;)</strong></a></span>&nbsp;or pay it in cash (in Argentine pesos) when you arrive at the Parque Nacional.</p>'
                                ],
                                [
                                    "lenguage_id" => 3,
                                    "name" => null,
                                    "description" => '<p>Voc&ecirc; dever&aacute; apresentar seu ingresso ao Parque Nacional. Pode comprar o ingresso aqui&nbsp;<span style="color: rgb(36, 113, 185);"><a style="color: rgb(36, 113, 185);" href="https://ventaweb.apn.gob.ar/reserva/inicio?dp=05" target="_blank" rel="noopener"><strong>(Selecionar: &ldquo;Acceso Corredor Rio Mitre y Glaciar Perito Moreno&rdquo;)</strong></a></span>&nbsp;ou pagar com dinheiro (pesos argentinos) ao chegar ao Parque Nacional.</p>'
                                ]
                            ]
                        ]
                    ],
                    "translables" => [
                        [
                            "lenguage_id" => 1,
                            "name" => "¿Qué llevar?",
                            "description" => null
                        ],
                        [
                            "lenguage_id" => 2,
                            "name" => "What to bring?",
                            "description" => null
                        ],
                        [
                            "lenguage_id" => 3,
                            "name" => "O que É PRECISO levar?",
                            "description" => null
                        ]
                    ]
                ];
        //9 restrictions
            $characteristics['characteristics'][] = [
                "icon_id" => null,
                "characteristic_type" => "restrictions",
                "order" => null,
                "icon" => null,

                "characteristics" => [],
                "translables" => [
                    [
                        "lenguage_id" => 1,
                        "name" => "Restricciones importantes antes de comprar",
                        "description" => '<p><span style="color: rgb(36, 113, 185);"><strong>Aclaraci&oacute;n:</strong></span> Esta excursi&oacute;n NO incluye caminata sobre el Glaciar Perito Moreno.</p>
                        <p style="text-align: justify;">&nbsp;</p>
                        <p><strong><span style="color: rgb(36, 113, 185);">No incluye:</span></strong>&nbsp;Entrada al Parque Nacional | Comida y bebida | Ropa personal adecuada a las condiciones clim&aacute;ticas de la regi&oacute;n. (fr&iacute;o, lluvia, viento, nieve).</p>
                        <p style="text-align: justify;">&nbsp;</p>
                        <p>Debido al grado de esfuerzo y dificultad que esta actividad presenta y con el solo objetivo de preservar la salud, no podrán participar de la excursión ciertas personas.</p>
                        <p style="text-align: justify;">&nbsp;</p>'
                    ],
                    [
                        "lenguage_id" => 2,
                        "name" => "Important restrictions before buying",
                        "description" => '<p><span style="color: rgb(36, 113, 185);"><strong>Important:</strong></span>&nbsp;This tour doesn&rsquo;t include walking on the Perito Moreno Glacier.</p>
                        <p style="text-align: justify;">&nbsp;</p>
                        <p><strong><span style="color: rgb(36, 113, 185);">Not included:</span></strong>&nbsp;Ticket to the National Park | Food and drink. | Personal clothes suitable for the weather conditions of the place. (cold, rain, wind, snow) </p>
                        <p style="text-align: justify;">&nbsp;</p>
                        <p>Due to the degree of effort and difficulty that this activity presents and with the sole objective of preserving health, certain people will not be able to participate in the excursion</p>
                        <p style="text-align: justify;">&nbsp;</p>'
                    ],
                    [
                        "lenguage_id" => 3,
                        "name" => "Restricoes importantes antes de comprar",
                        "description" => '<p><span style="color: rgb(36, 113, 185);"><strong>Esclarecimento:</strong></span>&nbsp; A excurs&atilde;o N&Atilde;O INCLUI caminhada sobre o Glaciar Perito Moreno.</p>
                        <p style="text-align: justify;">&nbsp;</p>
                        <p><span style="color: rgb(36, 113, 185);"><strong>N&atilde;o&nbsp;inclui:</strong></span>&nbsp; Ingresso ao Parque Nacional | Comida e bebida | Vestimenta pessoal adequada para as condi&ccedil;&otilde;es clim&aacute;ticas pr&oacute;prias da regi&atilde;o (frio, chuva, vento, neve).</p>
                        <p style="text-align: justify;">&nbsp;</p>
                        <p>Devido ao grau de esforço e dificuldade que esta atividade apresenta e com o único objetivo de preservar a saúde, certas pessoas ñao poderao participar da excursao</p>
                        <p style="text-align: justify;">&nbsp;</p>'
                    ]
                ]
            ];

        // 10 comparison_sail_perito 
            $characteristics['characteristics'][] = [
                "icon_id" => null,
                "characteristic_type" => "comparison_sail_perito",
                "order" => null,
                "icon" => null,

                "characteristics" => [],
                "translables" => [
                    [
                        "lenguage_id" => "1",
                        "name" => "Navega frente al Glaciar Perito Moreno",
                        "description" => "1"
                    ],
                    [
                        "lenguage_id" => "2",
                        "name" => "Sail in front of the Perito Moreno Glacier",
                        "description" => "1"
                    ],
                    [
                        "lenguage_id" => "3",
                        "name" => "Navegue em frente ao Glaciar Perito Moreno",
                        "description" => "1"
                    ]
                ]
            ];
        // 11 comparison_trekking_ice 
            $characteristics['characteristics'][] = [
                "icon_id" => null,
                "characteristic_type" => "comparison_trekking_ice",
                "order" => null,
                "icon" => null,

                "characteristics" => [],
                "translables" => [
                    [
                        "lenguage_id" => "1",
                        "name" => "Trekking sobre hielo",
                        "description" => "0"
                    ],
                    [
                        "lenguage_id" => "2",
                        "name" => "Ice trekking",
                        "description" => "0"
                    ],
                    [
                        "lenguage_id" => "3",
                        "name" => "Trekking no gelo",
                        "description" => "0"
                    ]
                ]
            ];
        // 12 comparison_dificult
            $characteristics['characteristics'][] = [
                "icon_id" => null,
                "characteristic_type" => "comparison_dificult",
                "order" => null,
                "icon" => null,

                "characteristics" => [],
                "translables" => [
                    [
                        "lenguage_id" => "1",
                        "name" => "Dificultad",
                        "description" => "Baja"
                    ],
                    [
                        "lenguage_id" => "2",
                        "name" => "Difficulty",
                        "description" => "Low"
                    ],
                    [
                        "lenguage_id" => "3",
                        "name" => "Dificuldade",
                        "description" => "Baixa"
                    ]
                ]
            ];
        // 14 comparison_fissures
            $characteristics['characteristics'][] = [
                "icon_id" => null,
                "characteristic_type" => "comparison_fissures",
                "order" => null,
                "icon" => null,

                "characteristics" => [],
                "translables" => [
                    [
                        "lenguage_id" => "1",
                        "name" => "Vista de grietas",
                        "description" => "Desde el barco"
                    ],
                    [
                        "lenguage_id" => "2",
                        "name" => "View of cracks",
                        "description" => "From the ship"
                    ],
                    [
                        "lenguage_id" => "3",
                        "name" => "Visão de rachaduras",
                        "description" => "Do barco"
                    ]
                ]
            ];
        //15 comparison_seracs
            $characteristics['characteristics'][] = [
                "icon_id" => null,
                "characteristic_type" => "comparison_seracs",
                "order" => null,
                "icon" => null,

                "characteristics" => [],
                "translables" => [
                    [
                        "lenguage_id" => "1",
                        "name" => "Vista de Seracs",
                        "description" => "1"
                    ],
                    [
                        "lenguage_id" => "2",
                        "name" => "View of Seracs",
                        "description" => "1"
                    ],
                    [
                        "lenguage_id" => "3",
                        "name" => "Visão de Seracs",
                        "description" => "1"
                    ]
                ]
            ];
        //16 comparison_sinks
            $characteristics['characteristics'][] = [
                "icon_id" => null,
                "characteristic_type" => "comparison_sinks",
                "order" => null,
                "icon" => null,

                "characteristics" => [],
                "translables" => [
                    [
                        "lenguage_id" => "1",
                        "name" => "Vista de sumideros",
                        "description" => "0"
                    ],
                    [
                        "lenguage_id" => "2",
                        "name" => "View of sinkholes",
                        "description" => "0"
                    ],
                    [
                        "lenguage_id" => "3",
                        "name" => "Visão dos sumidouros",
                        "description" => "0"
                    ]
                ]
            ];
        //17 comparison_caves
            $characteristics['characteristics'][] = [
                "icon_id" => null,
                "characteristic_type" => "comparison_caves",
                "order" => null,
                "icon" => null,

                "characteristics" => [],
                "translables" => [
                    [
                        "lenguage_id" => "1",
                        "name" => "Vista de cuevas",
                        "description" => "0"
                    ],
                    [
                        "lenguage_id" => "2",
                        "name" => "view of caves",
                        "description" => "0"
                    ],
                    [
                        "lenguage_id" => "3",
                        "name" => "vista das cavernas",
                        "description" => "0"
                    ]
                ]
            ];
        //18 comparison_laggons
            $characteristics['characteristics'][] = [
                "icon_id" => null,
                "characteristic_type" => "comparison_laggons",
                "order" => null,
                "icon" => null,
                "characteristics" => [],
                "translables" => [
                    [
                        "lenguage_id" => "1",
                        "name" => "Vista de lagunas",
                        "description" => "0"
                    ],
                    [
                        "lenguage_id" => "2",
                        "name" => "View of lagoons",
                        "description" => "0"
                    ],
                    [
                        "lenguage_id" => "3",
                        "name" => "Vista das lagoas",
                        "description" => "0"
                    ]
                ]
            ];
        //19 comparison_group_size
            $characteristics['characteristics'][] = [
                "icon_id" => null,
                "characteristic_type" => "comparison_group_size",
                "order" => null,
                "icon" => null,
                "characteristics" => [],
                "translables" => [
                    [
                        "lenguage_id" => "1",
                        "name" => "Tamaño de grupo",
                        "description" => "hasta 50"
                    ],
                    [
                        "lenguage_id" => "2",
                        "name" => "Group size",
                        "description" => "up to 50"
                    ],
                    [
                        "lenguage_id" => "3",
                        "name" => "Tamanho do grupo",
                        "description" => "até 50"
                    ]
                ]
            ];
        //20 comparison_lagoon_coast_trekking
            $characteristics['characteristics'][] = [
                "icon_id" => null,
                "characteristic_type" => "comparison_lagoon_coast_trekking",
                "order" => null,
                "icon" => null,
                "characteristics" => [],
                "translables" => [
                    [
                        "lenguage_id" => "1",
                        "name" => "Trekking por costa del lago",
                        "description" => "1"
                    ],
                    [
                        "lenguage_id" => "2",
                        "name" => "Trekking along the lake coast",
                        "description" => "1"
                    ],
                    [
                        "lenguage_id" => "3",
                        "name" => "Trekking ao longo da costa do lago",
                        "description" => "1"
                    ]
                ]
            ];
        //21 comparison_forest_trekking
            $characteristics['characteristics'][] = [
                "icon_id" => null,
                "characteristic_type" => "comparison_forest_trekking",
                "order" => null,
                "icon" => null,
                "characteristics" => [],
                "translables" => [
                    [
                        "lenguage_id" => "1",
                        "name" => "Trekking por bosque",
                        "description" => "1"
                    ],
                    [
                        "lenguage_id" => "2",
                        "name" => "Trekking through forest",
                        "description" => "1"
                    ],
                    [
                        "lenguage_id" => "3",
                        "name" => "Trekking pela floresta",
                        "description" => "1"
                    ]
                ]
            ];
        //22 comparison_food_included
            $characteristics['characteristics'][] = [
                "icon_id" => null,
                "characteristic_type" => "comparison_food_included",
                "order" => null,
                "icon" => null,
                "characteristics" => [],
                "translables" => [
                    [
                        "lenguage_id" => "1",
                        "name" => "Comida incluida",
                        "description" => "0"
                    ],
                    [
                        "lenguage_id" => "2",
                        "name" => "Lunch included",
                        "description" => "0"
                    ],
                    [
                        "lenguage_id" => "3",
                        "name" => "Almoço incluso",
                        "description" => "0"
                    ]
                ]
            ];
        //23 comparison_hotel_transfer
            $characteristics['characteristics'][] = [
                "icon_id" => null,
                "characteristic_type" => "comparison_hotel_transfer",
                "order" => null,
                "icon" => null,
                "characteristics" => [],
                "translables" => [
                    [
                        "lenguage_id" => "1",
                        "name" => "Traslado desde el hotel",
                        "description" => "optativo"
                    ],
                    [
                        "lenguage_id" => "2",
                        "name" => "Transfer from the hotel",
                        "description" => "optional"
                    ],
                    [
                        "lenguage_id" => "3",
                        "name" => "Traslado do hotel",
                        "description" => "opcional"
                    ]
                ]
            ];
        //25 comparison_current_price
            $characteristics['characteristics'][] = [
                "icon_id" => null,
                "characteristic_type" => "comparison_current_price",
                "order" => null,
                "icon" => null,
                "characteristics" => [],
                "translables" => [
                    [
                        "lenguage_id" => "1",
                        "name" => "Precio actual",
                        "description" => 15000
                    ],
                    [
                        "lenguage_id" => "2",
                        "name" => "Actual Price",
                        "description" => 15000
                    ],
                    [
                        "lenguage_id" => "3",
                        "name" => "Preço real",
                        "description" => 15000
                    ]
                ]
            ];
        //26 purchase_detail
            $characteristics['characteristics'][] = [
                "icon_id" => null,
                "characteristic_type" => 'purchase_detail',
                "order" => null,

                "characteristics" => [
                    [
                        "icon_id" => null,
                        "order" => null,
                        "icon" => null,
                        "characteristics" => [
                            // [
                            //     "icon_id" => null,
                            //     "order" => null,
                            //     "icon" => '$check',
                            //     "con_trf" => 1,
                            //     "characteristics" => [],
                            //     "translables" => [
                            //         [
                            //             "lenguage_id" => 1,
                            //             "name" => "Guía de Turismo bilingüe (español e inglés) en el bus",
                            //             "description" => ""
                            //         ],
                            //         [
                            //             "lenguage_id" => 2,
                            //             "name" => "Bilingual Tour Guide (Spanish and English) on the bus",
                            //             "description" => ""
                            //         ],
                            //         [
                            //             "lenguage_id" => 3,
                            //             "name" => "Guia turístico bilíngüe (espanhol e inglês) no ônibus",
                            //             "description" => ""
                            //         ]
                            //     ]
                            // ],
                            // [
                            //     "icon_id" => null,
                            //     "order" => null,
                            //     "icon" => '$check',
                            //     "con_trf" => 0,
                            //     "characteristics" => [],
                            //     "translables" => [
                            //         [
                            //             "lenguage_id" => 1,
                            //             "name" => "Visita de 2 horas aproximadamente a las pasarelas",
                            //             "description" => null
                            //         ],
                            //         [
                            //             "lenguage_id" => 2,
                            //             "name" => "Visit of approximately 2 hours to the catwalks",
                            //             "description" => null
                            //         ],
                            //         [
                            //             "lenguage_id" => 3,
                            //             "name" => "Visita de aproximadamente 2 horas às passarelas",
                            //             "description" => null
                            //         ]
                            //     ]
                            // ],
                            [
                                "icon_id" => null,
                                "order" => null,
                                "icon" => '$check',
                                "con_trf" => null,
                                "characteristics" => [],
                                "translables" => [
                                    [
                                        "lenguage_id" => 1,
                                        "name" => "Navegación frente a la pared sur del glaciar",
                                        "description" => null
                                    ],
                                    [
                                        "lenguage_id" => 2,
                                        "name" => "Navigation in front of the southern wall of the glacier",
                                        "description" => null
                                    ],
                                    [
                                        "lenguage_id" => 3,
                                        "name" => "Navegação em frente à parede sul da geleira",
                                        "description" => null
                                    ]
                                ]
                            ],
                            [
                                "icon_id" => null,
                                "order" => null,
                                "icon" => '$check',
                                "con_trf" => null,
                                "characteristics" => [],
                                "translables" => [
                                    [
                                        "lenguage_id" => 1,
                                        "name" => "Caminata por costa del lago hasta llegar al glaciar",
                                        "description" => null
                                    ],
                                    [
                                        "lenguage_id" => 2,
                                        "name" => "Walk along the coast of the lake until you reach the glacier",
                                        "description" => null
                                    ],
                                    [
                                        "lenguage_id" => 3,
                                        "name" => "Caminhe ao longo da costa do lago até chegar à geleira",
                                        "description" => null
                                    ]
                                ]
                            ],
                            [
                                "icon_id" => null,
                                "order" => null,
                                "icon" => '$check',
                                "con_trf" => null,
                                "characteristics" => [],
                                "translables" => [
                                    [
                                        "lenguage_id" => 1,
                                        "name" => "Mirador Panorámico",
                                        "description" => null
                                    ],
                                    [
                                        "lenguage_id" => 2,
                                        "name" => "Panoramic viewpoint",
                                        "description" => null
                                    ],
                                    [
                                        "lenguage_id" => 3,
                                        "name" => "Ponto de vista panorâmico",
                                        "description" => null
                                    ]
                                ]
                            ],
                            [
                                "icon_id" => null,
                                "order" => null,
                                "icon" => '$check',
                                "con_trf" => null,
                                "characteristics" => [],
                                "translables" => [
                                    [
                                        "lenguage_id" => 1,
                                        "name" => "Brindis",
                                        "description" => null
                                    ],
                                    [
                                        "lenguage_id" => 2,
                                        "name" => "Toast",
                                        "description" => null
                                    ],
                                    [
                                        "lenguage_id" => 3,
                                        "name" => "Brinde",
                                        "description" => null
                                    ]
                                ]
                            ],
                            [
                                "icon_id" => null,
                                "order" => null,
                                "icon" => '$check',
                                "con_trf" => 1,
                                "characteristics" => [],
                                "translables" => [
                                    [
                                        "lenguage_id" => 1,
                                        "name" => "CON TRASLADO: Inicio en su hotel de El Calafate",
                                        "description" => null
                                    ],
                                    [
                                        "lenguage_id" => 2,
                                        "name" => "WITH TRANSFER: Start at your hotel in El Calafate",
                                        "description" => null
                                    ],
                                    [
                                        "lenguage_id" => 3,
                                        "name" => "COM TRANSFER: Comece no seu hotel em El Calafate",
                                        "description" => null
                                    ]
                                ]
                            ],
                            [
                                "icon_id" => null,
                                "order" => null,
                                "icon" => '$check',
                                "con_trf" => 1,
                                "characteristics" => [],
                                "translables" => [
                                    [
                                        "lenguage_id" => 1,
                                        "name" => "CON TRASLADO: Traslado con guía y visita de aproximadamente 1 hora a pasarelas",
                                        "description" => null
                                    ],
                                    [
                                        "lenguage_id" => 2,
                                        "name" => "WITH TRANSFER: Optional transfer with guide, including a visit of about one hour to the walkways",
                                        "description" => null
                                    ],
                                    [
                                        "lenguage_id" => 3,
                                        "name" => "COM TRANSFER: Traslado opcional, com guia e visita de aproximadamente uma hora às passarelas",
                                        "description" => null
                                    ]
                                ]
                            ],
                        ],
                        "translables" => []
                    ]
                ],
                "translables" => [
                    [
                        "lenguage_id" => 1,
                        "name" => "Detalle de compra",
                        "description" => ""
                    ],
                    [
                        "lenguage_id" => 2,
                        "name" => "Purchase detail",
                        "description" => ""
                    ],
                    [
                        "lenguage_id" => 3,
                        "name" => "Detalhe da compra",
                        "description" => ""
                    ]
                ]
            ];

        //27 comparison_ratio
            $characteristics['characteristics'][] = [
                "icon_id" => null,
                "characteristic_type" => "comparison_ratio",
                "order" => null,
                "icon" => null,
                "characteristics" => [],
                "translables" => [
                    [
                        "lenguage_id" => "1",
                        "name" => "RATIO",
                        "description" => "0"
                    ],
                    [
                        "lenguage_id" => "2",
                        "name" => "RATIO",
                        "description" => "0"
                    ],
                    [
                        "lenguage_id" => "3",
                        "name" => "RATIO",
                        "description" => "0"
                    ]
                ]
            ];
        // 28 comparison_total_walk
            $characteristics['characteristics'][] = [
                "icon_id" => null,
                "characteristic_type" => "comparison_total_walk",
                "order" => null,
                "icon" => null,
                "characteristics" => [],
                "translables" => [
                    [
                        "lenguage_id" => "1",
                        "name" => "Caminata total",
                        "description" => "1 km aprox"
                    ],
                    [
                        "lenguage_id" => "2",
                        "name" => "Caminata total",
                        "description" => "1 km aprox"
                    ],
                    [
                        "lenguage_id" => "3",
                        "name" => "Caminata total",
                        "description" => "1 km aprox"
                    ]
                ]
            ];
        // 29 comparison_waterfall_view
            $characteristics['characteristics'][] = [
                "icon_id" => null,
                "characteristic_type" => "comparison_waterfall_view",
                "order" => null,
                "icon" => null,
                "characteristics" => [],
                "translables" => [
                    [
                        "lenguage_id" => "1",
                        "name" => "Visita a Cascada",
                        "description" => "0"
                    ],
                    [
                        "lenguage_id" => "2",
                        "name" => "Visita a Cascada",
                        "description" => "0"
                    ],
                    [
                        "lenguage_id" => "3",
                        "name" => "Visita a Cascada",
                        "description" => "0"
                    ]
                ]
            ];
        return $characteristics;
    }

    public function safariNautico()
    {
        $characteristics = [];

        //1 characteristics
            $characteristics['characteristics'][] = [
                # Generales"1"
                "icon_id" =>  NULL,
                "icon" =>  NULL,
                "characteristic_type" =>  "characteristics",
                "order" =>  NULL,
                #

                # translables
                    "translables" => [
                        [
                            "lenguage_id" =>  1,
                            "name" =>  "Características de la actividad",
                            "description" =>  NULL
                        ],
                        [
                            "lenguage_id" =>  2,
                            "name" =>  "Activity characteristic",
                            "description" =>  NULL
                        ],
                        [
                            "lenguage_id" =>  3,
                            "name" =>  "Característica da atividade",
                            "description" =>  NULL
                        ]
                    ],
                #

                # Las 6 características o ḿas
                #Translables
                "characteristics" =>
                [
                    #$clockConTraslado
                        [
                            "icon" =>  '$clock',
                            "order" =>  "1",
                            "translables" =>  [
                                [
                                    #ESPAÑOL
                                    "lenguage_id" =>  "1",
                                    "name"        =>  "<p>1 hora</p>",
                                    "description" =>  '<p>La duración de la actividad es aproximadamente 1 hora.</p>'
                                ],
                                [
                                    # INGLES
                                    "lenguage_id" =>  "2",
                                    "name"        =>  "<p>1 hour</p>",  
                                    "description" =>  "<p>The duration of the activity is approximately 1 hour.</p>"
                                ],
                                [
                                    # PORTUGUÉS
                                    "lenguage_id" =>  "3",
                                    "name"        =>  "<p>1 hora</p>",  
                                    "description" =>  "<p>A duração da atividade é de aproximadamente 1 hora.</p>"
                                ]
                            ]
                        ],
                    #$clockSinTraslado
                        // [
                        //     "icon" =>  '$clock',
                        //     "order" =>  "1",
                        //     "translables" =>  [
                        //         [
                        //             #ESPAÑOL
                        //             "lenguage_id" =>  "1",
                        //             "name"        =>  "Duración SIN traslado y pasarelas",
                        //             "description" =>  '<p>Aproximadamente 2.45 horas</p>'
                        //         ],
                        //         [
                        //             # INGLES
                        //             "lenguage_id" =>  "2",
                        //             "name"        =>  "Duration WITHOUT transfer and walkways",
                        //             "description" =>  "<p>Approximately 12 hours (Full day)</p>"
                        //         ],
                        //         [
                        //             # PORTUGUÉS
                        //             "lenguage_id" =>  "3",
                        //             "name"        =>  "Duração SEM traslado e passarelas",
                        //             "description" =>  "<p>Aproximadamente 12 horas (Dia inteiro)</p>"
                        //         ]
                        //     ]
                        // ],
                    #$calendar
                        [
                            "icon" =>  '$calendar',
                            "order" =>  "2",
                            "translables" =>  [
                                [
                                #ESPAÑOL
                                    "lenguage_id" =>  "1",
                                    "name" =>  "<p>Opera todo el año</p>",
                                    "description" => "<p>Esta actividad opera todo el año</p>"
                                ],
                                [
                                # INGLES
                                    "lenguage_id" =>  "2",
                                    "name" =>  "<p>Operates all year</p>",
                                    "description" => "<p>This activity operates throughout the year</p>"
                                ],
                                [
                                # PORTUGUÉS
                                    "lenguage_id" =>  "3",
                                    "name" =>  "<p>Año inteiro</p>",
                                    "description" => "<p>Esta atividade funciona durante todo o ano</p>"
                                ]
                            ]
                        ],
                    #$bus
                        [
                            "icon" =>  '$bus',
                            "order" =>  "3",
                            "translables" =>  [
                                [
                                #ESPAÑOL
                                    "lenguage_id" =>  "1",
                                    "name"        =>  "<p>Traslado no incluido</p>",                                    
                                    "description" =>  '<p>El traslado terrestre desde “El Calafate” al puerto “Bajo de las Sombras” NO está incluido.</p>'
                                ],
                                [
                                # INGLES
                                    "lenguage_id" =>  "2",
                                    "name"        =>  "<p>Transfer not included</p>",                                    
                                    "description" =>  "<p>Transfer from “El Calafate” to “Bajo de las Sombras” port is NOT included.</p>"
                                ],
                                [
                                # PORTUGUÉS
                                    "lenguage_id" =>  "3",
                                    "name"        =>  "<p>Transferência não incluída</p>",                                    
                                    "description" =>  "<p>A traslado de “El Calafate” ao porto “Bajo de las Sombras” NÃO está incluída.</p>"
                                ]
                            ]
                        ],
                    #$guide
                        [
                            "icon" =>  '$guide',
                            "order" =>  "4",
                            "translables" =>  [
                                [
                                #ESPAÑOL
                                    "lenguage_id" =>  "1",
                                    "name"        =>  "<p>Guías en español e inglés</p>",
                                    "description" =>  "<p>Nuestros guías de turismo hablan español e inglés</p>"
                                ],
                                [
                                # INGLES
                                    "lenguage_id" =>  "2",
                                    "name"        =>  "<p>Guides in Spanish and English</p>",
                                    "description" =>  "<p>Our tour guides speak Spanish and English</p>"
                                ],
                                [
                                # PORTUGUÉS
                                    "lenguage_id" =>  "3",
                                    "name"        =>  "<p>Guias em espanhol e inglês</p>",
                                    "description" =>  "<p>Nossos guias turísticos falam espanhol e inglês</p>"
                                ]
                            ]
                        ],
                    #$age
                        [
                            "icon" =>  '$age',
                            "order" =>  "5",
                            "translables" =>  [
                                [
                                #ESPAÑOL
                                    "lenguage_id" =>  "1",
                                    "name"        =>  '<p>Sin limites de edad</p>',
                                    "description" =>  '<p>Esta excursión no presenta una limitación de edad. Cualquier persona puede realizarla.</p>'
                                ],
                                [
                                # INGLES
                                    "lenguage_id" =>  "2",
                                    "name"        =>  '<p>No age limit</p>',
                                    "description" =>  '<p>This excursion does not present an age limitation. Anyone can do it.</p>'
                                ],
                                [
                                # PORTUGUÉS
                                    "lenguage_id" =>  "3",
                                    "name"        =>  '<p>Sem limite de idade</p>',
                                    "description" =>  '<p>Esta excursão não apresenta limite de idade. Qualquer um pode fazer isso.</p>'
                                ]
                            ]
                        ],
                    #$complexity
                        [
                            "icon" =>  '$complex',
                            "order" =>  "6",
                            "translables" =>  [
                                [
                                #ESPAÑOL
                                    "lenguage_id" =>  "1",
                                    "name"        =>  "<p>Complejidad nula</p>",
                                    "description" =>  "<p>Esta excursión no requiere esfuerzo físico.</p>"
                                ],
                                [
                                # INGLES
                                    "lenguage_id" =>  "2",
                                    "name"        =>  "<p>zero complexity</p>",
                                    "description" =>  "<p>No physical effort required.</p>"
                                ],
                                [
                                # PORTUGUÉS
                                    "lenguage_id" =>  "3",
                                    "name"        =>  "<p>complexidade zero</p>",
                                    "description" =>  "<p>Esta excursão não exige esforço físico.</p>"
                                ]
                            ]
                        ],
                    #$does_not_include
                        [
                            "icon" =>  '$complexity',
                            "order" =>  "7",
                            "translables" =>  [
                                [
                                #ESPAÑOL
                                    "lenguage_id" =>  "1",
                                    "name"        =>  "<p>Items no incluidos</p>",
                                    "description" =>  "<p><strong>No incluye:</strong> Entrada al Parque Nacional | Comida y bebida | Ropa personal adecuada a las condiciones climáticas de la región. (frío, lluvia, viento, nieve)</p>"
                                ],
                                [
                                # INGLES
                                    "lenguage_id" =>  "2",
                                    "name"        =>  "<p>Items not included</p>",
                                    "description" =>  "<p><strong>Not included:</strong> Entrance to the National Park | Food and drink | Personal clothing appropriate to the climatic conditions of the region. (cold, rain, wind, snow)</p>"
                                ],
                                [
                                # PORTUGUÉS
                                    "lenguage_id" =>  "3",
                                    "name"        =>  "<p>Itens não inclusos</p>",
                                    "description" =>  "<p><strong>Não inclui:</strong> Entrada no Parque Nacional | Comida e bebida | Roupa pessoal adequada às condições climáticas da região. (frio, chuva, vento, neve)</p>"
                                ]
                            ]
                        ]
                ]
            ];

        //2 about
            $characteristics['characteristics'][] = [
                    "icon_id" => null,
                    "icon" => null,
                    "characteristic_type" =>  "about",
                    "order" => null,

                    "characteristics" => [],
                    "translables" => [
                        [
                            "lenguage_id" => 1,
                            "name" => "Sobre esta experiencia",
                            "description" => '<p>La excursi&oacute;n comienza en el&nbsp;<span style="color: #2471B9;"><strong>puerto &ldquo;Bajo de las Sombras&rdquo;</strong></span>, ubicado en la Ruta 11, Km 70.9, a una hora y media de El Calafate y a solo 7km del Glaciar. El pasajero deber&aacute;&nbsp;<span style="color: #2471B9;"><strong>llegar por sus medios</strong></span>&nbsp;hasta el puerto y una vez all&iacute;, se embarcar&aacute; para&nbsp;<span style="color: #2471B9;"><strong>navegar</strong></span>&nbsp;por el Lago Rico, donde se podr&aacute; apreciar la impresionante&nbsp;<strong><span style="color: #2471B9;">pared de hielo del Glaciar Perito Moreno</span>&nbsp;</strong>y los t&eacute;mpanos que provienen de &eacute;l.</p>
                            <p>&nbsp;</p>
                            <p>Esta navegaci&oacute;n tiene una duraci&oacute;n de&nbsp;<span style="color: #2471B9;"><strong>una hora</strong></span>&nbsp;y brinda la posibilidad de&nbsp;<strong>observar</strong>&nbsp;desde nuestras&nbsp;<span style="color: #2471B9;"><strong>confortables embarcaciones</strong></span>, y con una perspectiva totalmente diferente, las impresionantes paredes del Glaciar Perito Moreno y sus&nbsp;<span style="color: #2471B9;"><strong>continuos y estruendosos derrumbes</strong></span>&nbsp;sobre las aguas del Lago Rico.</p>
                            <p>&nbsp;</p>
                            <p>A 400 metros de la pared sur del Glaciar Perito Moreno, el barco se detiene por unos minutos para poder observar m&aacute;s detalladamente el paisaje. &iexcl;Mas cerca de la&nbsp;<span style="color: #2471B9;"><strong>pared de la ruptura</strong></span>, imposible!</p>
                            <p>&nbsp;</p>
                            <p>El&nbsp;<strong>Safari N&aacute;utico</strong>&nbsp;es una navegaci&oacute;n&nbsp;<span style="color: #2471B9;"><strong>apta para todas las edades</strong></span>&nbsp;y se puede realizar los&nbsp;<span style="color: #2471B9;"><strong>365 d&iacute;as del a&ntilde;o.</strong></span></p>
                            <p>&nbsp;</p>
                            <p>Esta excursi&oacute;n est&aacute; orientada a personas que quieren contemplar la magia del hielo e inmortalizarla en la memoria, observando cada detalle de la pared de hielo y su entorno.</p>
                            <p>&nbsp;</p>
                            <p>Salidas desde puerto &ldquo;Bajo de las sombras&rdquo;: 10:00, 11:30 y 14:30 hs. (consultar por otras salidas)</p>
                            <p style="text-align: justify;">&nbsp;</p>'
                        ],
                        [
                            "lenguage_id" => 2,
                            "name" => "About",
                            "description" => '<p>The tour begins at the&nbsp;<span style="color: #2471B9;"><strong>&ldquo;Bajo de las Sombras&rdquo; port</strong></span>&nbsp;located in Ruta 11, Km 70.9, one hour and a half from El Calafate and just 7km from the glacier. You must get to the port by&nbsp;<span style="color: #2471B9;"><strong>your own means</strong></span>&nbsp;and, once there, you will board the catamaran boat to navigate the Lago Rico where your will be able to appreciate the amazing&nbsp;<span style="color: #2471B9;"><strong>ice wall of the Perito Moreno Glacier</strong></span>&nbsp;and its icebergs.</p>
                            <p>&nbsp;</p>
                            <p>In our&nbsp;<strong><span style="color: #2471B9;">comfortable</span>&nbsp;</strong>catamaran boat and with a completely different perspective, this&nbsp;<span style="color: #2471B9;"><strong>one-hour trip</strong></span>&nbsp;offers the possibility to&nbsp;<span style="color: #2471B9;"><strong>observe</strong></span>&nbsp;the spectacular ice walls of the Perito Moreno Glacier and its&nbsp;<span style="color: #2471B9;"><strong>continuous and thunderous collapses</strong></span>&nbsp;over the Lago Rico.</p>
                            <p>&nbsp;</p>
                            <p>400 meters from the southern wall of the Perito Moreno Glacier, the boat stops for a few minutes to take a closer look at the landscape. You couldn&rsquo;t get any closer to the&nbsp;<span style="color: #2471B9;"><strong>walls of the rupture</strong>!</span></p>
                            <p>&nbsp;</p>
                            <p>The &ldquo;<strong>Safari N&aacute;utico&rdquo;</strong>&nbsp;is a navigation&nbsp;<span style="color: #2471B9;"><strong>suitable for all ages</strong></span>&nbsp;and it can be done&nbsp;<span style="color: #2471B9;"><strong>year-round.</strong></span></p>
                            <p>&nbsp;</p>
                            <p>This tour is designed for people who want to enjoy the magic of the ice and to remember that experience forever, observing every detail of the ice wall and its surroundings.<br />Departures from &ldquo;Bajo de las Sombras&rdquo; port: 10:00, 11:30 and 14:30 hs. (Contact us about other departures)</p>
                            <p style="text-align: justify;">&nbsp;</p>'
                        ],
                        [
                            "lenguage_id" => 3,
                            "name" => "Sobre esta experiência",
                            "description" => '<p>A excurs&atilde;o come&ccedil;a no&nbsp;<span style="color: #2471B9;"><strong>porto &ldquo;Bajo de las Sombras&rdquo;</strong></span>&nbsp;localizado no quil&ocirc;metro 70,9 da Ruta 11, a uma hora e meia da cidade de El Calafate, e a apenas 7 km da geleira. O passageiro dever&aacute; chegar ao porto por&nbsp;<span style="color: #2471B9;"><strong>seus pr&oacute;prios meios</strong></span>. Ali, se embarcar&aacute; para navegar pelo Lago Rico e poder&aacute; apreciar a impressionante parede de gelo da Geleira Perito Moreno e os blocos de gelo que se desprendem.&nbsp;</p>
                            <p>&nbsp;</p>
                            <p>A navega&ccedil;&atilde;o dura&nbsp;<span style="color: #2471B9;"><strong>uma hora</strong></span>&nbsp;e oferece a oportunidade de&nbsp;<span style="color: #2471B9;"><strong>contemplar</strong></span>, em nossas&nbsp;<span style="color: #2471B9;"><strong>confort&aacute;veis embarca&ccedil;&otilde;es</strong></span>, e de uma perspectiva inteiramente diferente, as impressionantes paredes da Geleira Perito Moreno e seus&nbsp;<span style="color: #2471B9;"><strong>cont&iacute;nuos e estrondosos desabamentos</strong></span>&nbsp;sobre as &aacute;guas do Lago Rico.</p>
                            <p>&nbsp;</p>
                            <p>A 400 metros da parede sul da geleira, o barco se det&eacute;m alguns minutos para contemplar cada detalhe da paisagem. &Eacute; imposs&iacute;vel estar mais pr&oacute;ximo da&nbsp;<span style="color: #2471B9;"><strong>parede do desabamento</strong>!</span></p>
                            <p>&nbsp;</p>
                            <p>O &ldquo;<strong><span style="color: #2471B9;">Safari n&aacute;utico</span>&rdquo;</strong>&nbsp;&eacute; um tipo de navega&ccedil;&atilde;o&nbsp;<span style="color: #2471B9;"><strong>apta para todas as idades</strong></span>&nbsp;e est&aacute; dispon&iacute;vel nos&nbsp;<strong><span style="background-color: #2471B9;">365 dias do ano</span>.</strong></p>
                            <p>&nbsp;</p>
                            <p>Esta excurs&atilde;o est&aacute; destinada a pessoas que desejam contemplar a magia do gelo e guardar em sua mem&oacute;ria cada detalhe da parede de gelo e de seu entorno.</p>
                            <p>&nbsp;</p>
                            <p>Saidas desde porto &ldquo;Bajo de las Sombras&rdquo;:&nbsp;10:00, 11:30 y 14:30&nbsp;hs. (Contacte-nos sobre outras partidas)</p>
                            <p style="text-align: justify;">&nbsp;</p>'
                        ]
                    ]
            ];

        //3 before_buying 
            $characteristics['characteristics'][] = [
                "icon_id" => null,
                "characteristic_type" => "before_buying",
                "order" => null,
                "characteristics" => [
                    [
                        "icon" => null,
                        "order" => null,
                        "characteristics" => [
                            [
                                "icon_id" => null,
                                "icon" => '$age_yellow',
                                "order" => null,
                                "translables" =>  [
                                    [
                                    #ESPAÑOL
                                        "lenguage_id" =>  "1",
                                        "name"        =>  '<p>Sin limites de edad</p>',
                                        "description" =>  '<p>Esta excursión no presenta una limitación de edad. Cualquier persona puede realizarla.</p>'
                                    ],
                                    [
                                    # INGLES
                                        "lenguage_id" =>  "2",
                                        "name"        =>  '<p>No age limit</p>',
                                        "description" =>  '<p>This excursion does not present an age limitation. Anyone can do it.</p>'
                                    ],
                                    [
                                    # PORTUGUÉS
                                        "lenguage_id" =>  "3",
                                        "name"        =>  '<p>Sem limite de idade</p>',
                                        "description" =>  '<p>Esta excursão não apresenta limite de idade. Qualquer um pode fazer isso.</p>'
                                    ]
                                ]
                            ],
                        ],
                        "translables" => []
                    ],
                ],
                "translables" => [
                    [
                        "lenguage_id" => 1,
                        "name" => "A TENER EN CUENTA ANTES DE COMPRAR",
                        "description" => '<p><span style="color: #2471B9;"><strong>Para realizar esta excursi&oacute;n deber&aacute;s acercarte por tus propios medios al Puerto Bajo las Sombras</strong></span>, ubicado en la Ruta 11 km 70.9. Te sugerimos salir con 1 hora y media de anticipaci&oacute;n de El Calafate para llegar a horario. El camino dentro del Parque Nacional es sinuoso y por seguridad, te recomendamos respetar los l&iacute;mites de velocidad se&ntilde;alados.</p>
                        <p>&nbsp;</p>
                        <p><span style="color: #2471B9;"><strong>Los tickets pueden ser comprados online o en el puerto antes de embarcar. En los meses de verano, recomendamos realizar la compra anticipadamente.</strong></span></p>
                        <p>&nbsp;</p>
                        <p><strong>No incluye:</strong>&nbsp;Entrada al Parque Nacional.</p>
                        <p style="text-align: justify;">&nbsp;</p>'
                    ],
                    [
                        "lenguage_id" => 2,
                        "name" => "BEFORE PURCHASING YOUR TICKETS, PLEASE KEEP IN MIND THE FOLLOWING:",
                        "description" => '<p><strong><span style="color: #2471B9;">To do the tour you should get to the Bajo las Sombras Port</span>,&nbsp;</strong>located on Ruta 11 km 70.9, by your own means. We suggest setting off from El Calafate one hour in advance to be able to get on time. The road within the National Park is winding so, for your safety, we recommend that you comply with the speed limits.</p>
                        <p>&nbsp;</p>
                        <p><span style="color: #2471B9;"><strong>The tickets can be bought online or in the port before boarding. In the summer months, we recommend buying the tickets in advance.</strong></span></p>
                        <p>&nbsp;</p>
                        <p><strong>Not included:&nbsp;</strong>National Park&rsquo;s entrance fee.</p>
                        <p style="text-align: justify;">&nbsp;</p>'
                    ],
                    [
                        "lenguage_id" => 3,
                        "name" => "LEVAR EM CONTA ANTES DE COMPRAR",
                        "description" => '<p><strong><span style="color: #2471B9;">Para participar desta excurs&atilde;o voc&ecirc; dever&aacute; ir, por seus pr&oacute;prios meios, para o Porto Bajos las Sombras</span>,&nbsp;</strong>localizado no quil&ocirc;metro 70,9 da Ruta 11. &Eacute; recomend&aacute;vel sair da cidade de El Calafate com una antecipa&ccedil;&atilde;o de uma hora e meia, para chegar a tempo. O caminho no Parque Nacional &eacute; sinuoso e, para sua seguran&ccedil;a, recomendamos respeitar os limites de velocidade indicados.</p>
                        <p>&nbsp;</p>
                        <p><span style="color: #2471B9;"><strong>Os t&iacute;quetes podem ser adquiridos online ou no porto, antes de se embarcar. Recomendamos adquirir os t&iacute;quetes antecipadamente, nos meses de ver&atilde;o.</strong></span></p>
                        <p>&nbsp;</p>
                        <p><strong>N&atilde;o inclu&iacute;do:&nbsp;</strong>Ingresso ao Parque Nacional.</p>
                        <p style="text-align: justify;">&nbsp;</p>'
                    ]
                ]
            ];

        //5 itinerary 
            $characteristics['characteristics'][] = [
                "icon_id" => null,
                "characteristic_type" => 'itinerary',
                "order" => null,

                "characteristics" => [
                    [
                        "icon_id" => null,
                        "order" => null,
                        "icon" => null,
                        "characteristics" => [
                            [
                                "icon_id" => null,
                                "order" => null,
                                "icon" => '$boarding',
                                "characteristics" => [],
                                "translables" => [
                                    [
                                        "lenguage_id" => 1,
                                        "name" => "Embarque en Puerto bajo de las Sombras",
                                        "description" => null
                                    ],
                                    [
                                        "lenguage_id" => 2,
                                        "name" => "Boarding in Puerto bajo de las Sombras",
                                        "description" => null
                                    ],
                                    [
                                        "lenguage_id" => 3,
                                        "name" => "Embarque no Porto bajo de las Sombras",
                                        "description" => null
                                    ]
                                ]
                            ],
                            [
                                "icon_id" => null,
                                "order" => null,
                                "icon" => '$itinerary_ship',
                                "characteristics" => [],
                                "translables" => [
                                    [
                                        "lenguage_id" => 1,
                                        "name" => "Navegación",
                                        "description" => "1 hora"
                                    ],
                                    [
                                        "lenguage_id" => 2,
                                        "name" => "Navegation",
                                        "description" => "1 hour"
                                    ],
                                    [
                                        "lenguage_id" => 3,
                                        "name" => "Navegação",
                                        "description" => "1 hora"
                                    ]
                                ]
                            ],
                            [
                                "icon_id" => null,
                                "order" => null,
                                "icon" => '$landing',
                                "characteristics" => [],
                                "translables" => [
                                    [
                                        "lenguage_id" => 1,
                                        "name" => "Desembarco y fin de la excursión",
                                        "description" => null
                                    ],
                                    [
                                        "lenguage_id" => 2,
                                        "name" => "Disembarkation and end of the excursion",
                                        "description" => null
                                    ],
                                    [
                                        "lenguage_id" => 3,
                                        "name" => "Desembarque e fim da excursão",
                                        "description" => null
                                    ]
                                ]
                            ]
                        ],
                        "translables" => []
                    ],
                    [
                        "icon_id" => null,
                        "order" => null,
                        "icon" => null,
                        "characteristics" => [
                            [
                                "icon_id" => null,
                                "order" => null,
                                "icon" => null,
                                "characteristics" => [],
                                "translables" => [
                                    [
                                        "lenguage_id" => 1,
                                        "name" => null,
                                        "description" => null
                                    ],
                                    [
                                        "lenguage_id" => 2,
                                        "name" => null,
                                        "description" => null
                                    ],
                                    [
                                        "lenguage_id" => 3,
                                        "name" => null,
                                        "description" => null
                                    ]
                                ]
                            ],
                            [
                                "icon_id" => null,
                                "order" => null,
                                "icon" => null,
                                "characteristics" => [],
                                "translables" => [
                                    [
                                        "lenguage_id" => 1,
                                        "name" => "TOUR INCLUIDO",
                                        "description" => null
                                    ],
                                    [
                                        "lenguage_id" => 2,
                                        "name" => "TOUR INCLUDED",
                                        "description" => null
                                    ],
                                    [
                                        "lenguage_id" => 3,
                                        "name" => "PASSEIO INCLUÍDO",
                                        "description" => null
                                    ]
                                ]
                            ]
                        ],
                        "translables" => []
                    ]
                ],
                "translables" => [
                    [
                        "lenguage_id" => 1,
                        "name" => "Itinerario Safari Nautico",
                        "description" => "Este itinerario es orientativo y puede variar el orden."
                    ],
                    [
                        "lenguage_id" => 2,
                        "name" => "Safari Nautico Itineray",
                        "description" => "This itinerary is merely illustrative and the order of activities may change."
                    ],
                    [
                        "lenguage_id" => 3,
                        "name" => "Itinerário Safari Nautico",
                        "description" => "Este itinerário é apenas orientativo e a ordem pode mudar."
                    ]
                ]
            ];

        //7 carry
            $characteristics['characteristics'][] =
                [
                    "icon_id" => null,
                    "characteristic_type" => "carry",
                    "order" => null,
                    "icon" => null,
                    "characteristics" => [
                        [
                            "icon_id" => null,
                            "order" => null,
                            "icon" => '$cloth',
                            "characteristics" => [],
                            "translables" => [
                                [
                                    "lenguage_id" => 1,
                                    "name" => null,
                                    "description" => '<p>Vestir ropa c&oacute;moda y abrigada. Campera, calzado deportivo o botas de trekking, lentes de sol, protector solar, guantes, gorro.</p>'
                                ],
                                [
                                    "lenguage_id" => 2,
                                    "name" => null,
                                    "description" => '<p>Wear comfortable and warm clothes. A jacket, sports shoes or trekking boots, sunglasses, sunscreen, gloves, a wool hat.</p>'
                                ],
                                [
                                    "lenguage_id" => 3,
                                    "name" => null,
                                    "description" => '<p>Roupa confort&aacute;vel e quente. Casaco, cal&ccedil;ado esportivo ou botas de trekking, &oacute;culos de sol, protetor solar, luvas e gorro.</p>'
                                ]
                            ]
                        ],
                        [
                            "icon_id" => null,
                            "order" => null,
                            "icon" => '$food',
                            "characteristics" => [],
                            "translables" => [
                                [
                                    "lenguage_id" => 1,
                                    "name" => null,
                                    "description" => '<p>Llevar comida y bebida para el d&iacute;a. La Empresa no cuenta con servicio de venta de comidas ni bebidas.</p>'
                                ],
                                [
                                    "lenguage_id" => 2,
                                    "name" => null,
                                    "description" => '<p>Bring food and drink for the day. The company does not sell food and drinks</p>'
                                ],
                                [
                                    "lenguage_id" => 3,
                                    "name" => null,
                                    "description" => '<p>Levar comida e bebida para todo o dia. A empresa n&atilde;o oferece servi&ccedil;o de venda de comidas nem bebidas.</p>'
                                ]
                            ]
                        ],
                        [
                            "icon_id" => null,
                            "characteristic_type" => null,
                            "order" => null,
                            "icon" => '$ticket',
                            "characteristics" => [],
                            "translables" => [
                                [
                                    "lenguage_id" => 1,
                                    "name" => null,
                                    "description" => '<p>Deber&aacute;s presentar tu entrada al Parque Nacional. Pod&eacute;s comprarla&nbsp;<a href="https://ventaweb.apn.gob.ar/reserva/inicio?dp=05" target="_blank" rel="noopener"><strong>ac&aacute;<span style="color: #2471B9;"> (Seleccionar: &ldquo;Acceso Corredor Rio Mitre y Glaciar Perito Moreno&rdquo;)</span></strong></a>&nbsp;o abonarla en efectivo (en pesos argentinos) al llegar al Parque Nacional.</p>'
                                ],
                                [
                                    "lenguage_id" => 2,
                                    "name" => null,
                                    "description" => '<p>Tickets must be exhibited at the entrance of the Parque Nacional. You can buy your ticket here&nbsp;<span style="color: #2471B9;"><a style="color: #2471B9;" href="https://ventaweb.apn.gob.ar/reserva/inicio?dp=05" target="_blank" rel="noopener"><strong>(Select: &ldquo;Acceso Corredor Rio Mitre y Glaciar Perito Moreno&rdquo;)</strong></a></span>&nbsp;or pay it in cash (in Argentine pesos) when you arrive at the Parque Nacional.</p>'
                                ],
                                [
                                    "lenguage_id" => 3,
                                    "name" => null,
                                    "description" => '<p>Voc&ecirc; dever&aacute; apresentar seu ingresso ao Parque Nacional. Pode comprar o ingresso aqui&nbsp;<span style="color: #2471B9;"><a style="color: #2471B9;" href="https://ventaweb.apn.gob.ar/reserva/inicio?dp=05" target="_blank" rel="noopener"><strong>(Selecionar: &ldquo;Acceso Corredor Rio Mitre y Glaciar Perito Moreno&rdquo;)</strong></a>&nbsp;</span>ou pagar com dinheiro (pesos argentinos) ao chegar ao Parque Nacional.</p>'
                                ]
                            ]
                        ]
                    ],
                    "translables" => [
                        [
                            "lenguage_id" => 1,
                            "name" => "¿Qué llevar?",
                            "description" => null
                        ],
                        [
                            "lenguage_id" => 2,
                            "name" => "What to bring?",
                            "description" => null
                        ],
                        [
                            "lenguage_id" => 3,
                            "name" => "O que É PRECISO levar?",
                            "description" => null
                        ]
                    ]
                ];
        //9 restrictions
            $characteristics['characteristics'][] = [
                "icon_id" => null,
                "characteristic_type" => "restrictions",
                "order" => null,
                "icon" => null,

                "characteristics" => [],
                "translables" => [
                    [
                        "lenguage_id" => 1,
                        "name" => "A tener en cuenta antes de comprar",
                        "description" => '<p><span style="color: #2471B9;"><strong>Para realizar esta excursi&oacute;n deber&aacute;s acercarte por tus propios medios al Puerto Bajo las Sombras</strong></span>, ubicado en la Ruta 11 km 70.9. Te sugerimos salir con 1 hora y media de anticipaci&oacute;n de El Calafate para llegar a horario. El camino dentro del Parque Nacional es sinuoso y por seguridad, te recomendamos respetar los l&iacute;mites de velocidad se&ntilde;alados.</p>
                        <p>&nbsp;</p>
                        <p><span style="color: #2471B9;"><strong>Los tickets pueden ser comprados online o en el puerto antes de embarcar. En los meses de verano, recomendamos realizar la compra anticipadamente.</strong></span></p>
                        <p>&nbsp;</p>
                        <p><strong>No incluye:</strong>&nbsp;Entrada al Parque Nacional.</p>
                        <p style="text-align: justify;">&nbsp;</p>'
                    ],
                    [
                        "lenguage_id" => 2,
                        "name" => "To keep in mind before buying",
                        "description" => '<p><strong><span style="color: #2471B9;">To do the tour you should get to the Bajo las Sombras Port</span>,&nbsp;</strong>located on Ruta 11 km 70.9, by your own means. We suggest setting off from El Calafate one hour in advance to be able to get on time. The road within the National Park is winding so, for your safety, we recommend that you comply with the speed limits.</p>
                        <p>&nbsp;</p>
                        <p><span style="color: #2471B9;"><strong>The tickets can be bought online or in the port before boarding. In the summer months, we recommend buying the tickets in advance.</strong></span></p>
                        <p>&nbsp;</p>
                        <p><strong>Not included:&nbsp;</strong>National Park&rsquo;s entrance fee.</p>
                        <p style="text-align: justify;">&nbsp;</p>'
                    ],
                    [
                        "lenguage_id" => 3,
                        "name" => "Para ter em mente antes de comprar",
                        "description" => '<p><strong><span style="color: #2471B9;">Para participar desta excurs&atilde;o voc&ecirc; dever&aacute; ir, por seus pr&oacute;prios meios, para o Porto Bajos las Sombras</span>,&nbsp;</strong>localizado no quil&ocirc;metro 70,9 da Ruta 11. &Eacute; recomend&aacute;vel sair da cidade de El Calafate com una antecipa&ccedil;&atilde;o de uma hora e meia, para chegar a tempo. O caminho no Parque Nacional &eacute; sinuoso e, para sua seguran&ccedil;a, recomendamos respeitar os limites de velocidade indicados.</p>
                        <p>&nbsp;</p>
                        <p><span style="color: #2471B9;"><strong>Os t&iacute;quetes podem ser adquiridos online ou no porto, antes de se embarcar. Recomendamos adquirir os t&iacute;quetes antecipadamente, nos meses de ver&atilde;o.</strong></span></p>
                        <p>&nbsp;</p>
                        <p><strong>N&atilde;o inclu&iacute;do:&nbsp;</strong>Ingresso ao Parque Nacional.</p>
                        <p style="text-align: justify;">&nbsp;</p>'
                    ]
                ]
            ];

        // 10 comparison_sail_perito 
            $characteristics['characteristics'][] = [
                "icon_id" => null,
                "characteristic_type" => "comparison_sail_perito",
                "order" => null,
                "icon" => null,

                "characteristics" => [],
                "translables" => [
                    [
                        "lenguage_id" => "1",
                        "name" => "Navega frente al Glaciar Perito Moreno",
                        "description" => "1"
                    ],
                    [
                        "lenguage_id" => "2",
                        "name" => "Sail in front of the Perito Moreno Glacier",
                        "description" => "1"
                    ],
                    [
                        "lenguage_id" => "3",
                        "name" => "Navegue em frente ao Glaciar Perito Moreno",
                        "description" => "1"
                    ]
                ]
            ];
        // 11 comparison_trekking_ice 
            $characteristics['characteristics'][] = [
                "icon_id" => null,
                "characteristic_type" => "comparison_trekking_ice",
                "order" => null,
                "icon" => null,

                "characteristics" => [],
                "translables" => [
                    [
                        "lenguage_id" => "1",
                        "name" => "Trekking sobre hielo",
                        "description" => "0"
                    ],
                    [
                        "lenguage_id" => "2",
                        "name" => "Ice trekking",
                        "description" => "0"
                    ],
                    [
                        "lenguage_id" => "3",
                        "name" => "Trekking no gelo",
                        "description" => "0"
                    ]
                ]
            ];
        // 12 comparison_dificult
            $characteristics['characteristics'][] = [
                "icon_id" => null,
                "characteristic_type" => "comparison_dificult",
                "order" => null,
                "icon" => null,

                "characteristics" => [],
                "translables" => [
                    [
                        "lenguage_id" => "1",
                        "name" => "Dificultad",
                        "description" => "Baja"
                    ],
                    [
                        "lenguage_id" => "2",
                        "name" => "Difficulty",
                        "description" => "Low"
                    ],
                    [
                        "lenguage_id" => "3",
                        "name" => "Dificuldade",
                        "description" => "baixa"
                    ]
                ]
            ];
        // 14 comparison_fissures
            $characteristics['characteristics'][] = [
                "icon_id" => null,
                "characteristic_type" => "comparison_fissures",
                "order" => null,
                "icon" => null,

                "characteristics" => [],
                "translables" => [
                    [
                        "lenguage_id" => "1",
                        "name" => "Vista de grietas",
                        "description" => "Desde el barco"
                    ],
                    [
                        "lenguage_id" => "2",
                        "name" => "view of cracks",
                        "description" => "From the ship"
                    ],
                    [
                        "lenguage_id" => "3",
                        "name" => "visão de rachaduras",
                        "description" => "Do barco"
                    ]
                ]
            ];
        //15 comparison_seracs
            $characteristics['characteristics'][] = [
                "icon_id" => null,
                "characteristic_type" => "comparison_seracs",
                "order" => null,
                "icon" => null,

                "characteristics" => [],
                "translables" => [
                    [
                        "lenguage_id" => "1",
                        "name" => "Vista de Seracs",
                        "description" => "Desde el barco"
                    ],
                    [
                        "lenguage_id" => "2",
                        "name" => "View of Seracs",
                        "description" => "From the ship"
                    ],
                    [
                        "lenguage_id" => "3",
                        "name" => "Visão de Seracs",
                        "description" => "Do barco"
                    ]
                ]
            ];
        //16 comparison_sinks
            $characteristics['characteristics'][] = [
                "icon_id" => null,
                "characteristic_type" => "comparison_sinks",
                "order" => null,
                "icon" => null,

                "characteristics" => [],
                "translables" => [
                    [
                        "lenguage_id" => "1",
                        "name" => "Vista de sumideros",
                        "description" => "0"
                    ],
                    [
                        "lenguage_id" => "2",
                        "name" => "View of sinkholes",
                        "description" => "0"
                    ],
                    [
                        "lenguage_id" => "3",
                        "name" => "Visão dos sumidouros",
                        "description" => "0"
                    ]
                ]
            ];
        //17 comparison_caves
            $characteristics['characteristics'][] = [
                "icon_id" => null,
                "characteristic_type" => "comparison_caves",
                "order" => null,
                "icon" => null,

                "characteristics" => [],
                "translables" => [
                    [
                        "lenguage_id" => "1",
                        "name" => "Vista de cuevas",
                        "description" => "0"
                    ],
                    [
                        "lenguage_id" => "2",
                        "name" => "view of caves",
                        "description" => "0"
                    ],
                    [
                        "lenguage_id" => "3",
                        "name" => "vista das cavernas",
                        "description" => "0"
                    ]
                ]
            ];
        //18 comparison_laggons
            $characteristics['characteristics'][] = [
                "icon_id" => null,
                "characteristic_type" => "comparison_laggons",
                "order" => null,
                "icon" => null,
                "characteristics" => [],
                "translables" => [
                    [
                        "lenguage_id" => "1",
                        "name" => "Vista de lagunas",
                        "description" => "0"
                    ],
                    [
                        "lenguage_id" => "2",
                        "name" => "View of lagoons",
                        "description" => "0"
                    ],
                    [
                        "lenguage_id" => "3",
                        "name" => "Vista das lagoas",
                        "description" => "0"
                    ]
                ]
            ];
        //19 comparison_group_size
            $characteristics['characteristics'][] = [
                "icon_id" => null,
                "characteristic_type" => "comparison_group_size",
                "order" => null,
                "icon" => null,
                "characteristics" => [],
                "translables" => [
                    [
                        "lenguage_id" => "1",
                        "name" => "Tamaño de grupo",
                        "description" => "hasta 150"
                    ],
                    [
                        "lenguage_id" => "2",
                        "name" => "group size",
                        "description" => "up to 150"
                    ],
                    [
                        "lenguage_id" => "3",
                        "name" => "Tamanho do grupo",
                        "description" => "até 150"
                    ]
                ]
            ];
        //20 comparison_lagoon_coast_trekking
            $characteristics['characteristics'][] = [
                "icon_id" => null,
                "characteristic_type" => "comparison_lagoon_coast_trekking",
                "order" => null,
                "icon" => null,
                "characteristics" => [],
                "translables" => [
                    [
                        "lenguage_id" => "1",
                        "name" => "Trekking por costa del lago",
                        "description" => "0"
                    ],
                    [
                        "lenguage_id" => "2",
                        "name" => "Trekking along the lake coast",
                        "description" => "0"
                    ],
                    [
                        "lenguage_id" => "3",
                        "name" => "Trekking ao longo da costa do lago",
                        "description" => "0"
                    ]
                ]
            ];
        //21 comparison_forest_trekking
            $characteristics['characteristics'][] = [
                "icon_id" => null,
                "characteristic_type" => "comparison_forest_trekking",
                "order" => null,
                "icon" => null,
                "characteristics" => [],
                "translables" => [
                    [
                        "lenguage_id" => "1",
                        "name" => "Trekking por bosque",
                        "description" => "0"
                    ],
                    [
                        "lenguage_id" => "2",
                        "name" => "trekking through forest",
                        "description" => "0"
                    ],
                    [
                        "lenguage_id" => "3",
                        "name" => "trekking pela floresta",
                        "description" => "0"
                    ]
                ]
            ];
        //22 comparison_food_included
            $characteristics['characteristics'][] = [
                "icon_id" => null,
                "characteristic_type" => "comparison_food_included",
                "order" => null,
                "icon" => null,
                "characteristics" => [],
                "translables" => [
                    [
                        "lenguage_id" => "1",
                        "name" => "Comida incluida",
                        "description" => "0"
                    ],
                    [
                        "lenguage_id" => "2",
                        "name" => "Lunch included",
                        "description" => "0"
                    ],
                    [
                        "lenguage_id" => "3",
                        "name" => "Almoço incluso",
                        "description" => "0"
                    ]
                ]
            ];
        //23 comparison_hotel_transfer
            $characteristics['characteristics'][] = [
                "icon_id" => null,
                "characteristic_type" => "comparison_hotel_transfer",
                "order" => null,
                "icon" => null,
                "characteristics" => [],
                "translables" => [
                    [
                        "lenguage_id" => "1",
                        "name" => "Traslado desde el hotel",
                        "description" => "0"
                    ],
                    [
                        "lenguage_id" => "2",
                        "name" => "Transfer from the hotel",
                        "description" => "0"
                    ],
                    [
                        "lenguage_id" => "3",
                        "name" => "Traslado do hotel",
                        "description" => "0"
                    ]
                ]
            ];
        //25 comparison_current_price
            $characteristics['characteristics'][] = [
                "icon_id" => null,
                "characteristic_type" => "comparison_current_price",
                "order" => null,
                "icon" => null,
                "characteristics" => [],
                "translables" => [
                    [
                        "lenguage_id" => "1",
                        "name" => "Precio actual",
                        "description" => 6000
                    ],
                    [
                        "lenguage_id" => "2",
                        "name" => "Actual Price",
                        "description" => 6000
                    ],
                    [
                        "lenguage_id" => "3",
                        "name" => "Preço real",
                        "description" => 6000
                    ]
                ]
            ];

        //26 purchase_detail
            $characteristics['characteristics'][] = [
                "icon_id" => null,
                "characteristic_type" => 'purchase_detail',
                "order" => null,

                "characteristics" => [
                    [
                        "icon_id" => null,
                        "order" => null,
                        "icon" => null,
                        "characteristics" => [
                            [
                                "icon_id" => null,
                                "order" => null,
                                "icon" => '$check',
                                "con_trf" => null,
                                "characteristics" => [],
                                "translables" => [
                                    [
                                        "lenguage_id" => 1,
                                        "name" => "Embarque: Puerto Bajo de las Sombras",
                                        "description" => null
                                    ],
                                    [
                                        "lenguage_id" => 2,
                                        "name" => "Embarkation: Lower Port of Shadows",
                                        "description" => null
                                    ],
                                    [
                                        "lenguage_id" => 3,
                                        "name" => "Embarque: Porto Inferior das Sombras",
                                        "description" => null
                                    ]
                                ]
                            ],
                            [
                                "icon_id" => null,
                                "order" => null,
                                "icon" => '$check',
                                "con_trf" => null,
                                "characteristics" => [],
                                "translables" => [
                                    [
                                        "lenguage_id" => 1,
                                        "name" => "Navegación de 1 hora frente a la cara sur del Glaciar Perito Moreno",
                                        "description" => ""
                                    ],
                                    [
                                        "lenguage_id" => 2,
                                        "name" => "1-hour navigation in front of the south face of the Perito Moreno Glacier",
                                        "description" => ""
                                    ],
                                    [
                                        "lenguage_id" => 3,
                                        "name" => "Navegação de 1 hora em frente à face sul do Glaciar Perito Moreno",
                                        "description" => ""
                                    ]
                                ]
                            ],
                            [
                                "icon_id" => null,
                                "order" => null,
                                "icon" => '$check',
                                "con_trf" => null,
                                "characteristics" => [],
                                "translables" => [
                                    [
                                        "lenguage_id" => 1,
                                        "name" => "Esta excursión no incluye traslado desde y hacia El Calafate. Tené en cuenta que la distancia entre El Calafate y el Parque Nacional Los Glaciares es de 80km",
                                        "description" => null
                                    ],
                                    [
                                        "lenguage_id" => 2,
                                        "name" => "This excursion does not include transportation to and from El Calafate. Keep in mind that the distance between El Calafate and Los Glaciares National Park is 80km",
                                        "description" => null
                                    ],
                                    [
                                        "lenguage_id" => 3,
                                        "name" => "Esta excursão não inclui transporte de e para El Calafate. Tenha em mente que a distância entre El Calafate e o Parque Nacional Los Glaciares é de 80 km",
                                        "description" => null
                                    ]
                                ]
                            ]
                        ],
                        "translables" => []
                    ]
                ],
                "translables" => [
                    [
                        "lenguage_id" => 1,
                        "name" => "Detalle de compra",
                        "description" => ""
                    ],
                    [
                        "lenguage_id" => 2,
                        "name" => "Purchase detail",
                        "description" => ""
                    ],
                    [
                        "lenguage_id" => 3,
                        "name" => "Detalhe da compra",
                        "description" => ""
                    ]
                ]
            ];
        //27 comparison_ratio
            $characteristics['characteristics'][] = [
                "icon_id" => null,
                "characteristic_type" => "comparison_ratio",
                "order" => null,
                "icon" => null,
                "characteristics" => [],
                "translables" => [
                    [
                        "lenguage_id" => "1",
                        "name" => "RATIO",
                        "description" => "0"
                    ],
                    [
                        "lenguage_id" => "2",
                        "name" => "RATIO",
                        "description" => "0"
                    ],
                    [
                        "lenguage_id" => "3",
                        "name" => "RATIO",
                        "description" => "0"
                    ]
                ]
            ];
        // 28 comparison_total_walk
            $characteristics['characteristics'][] = [
                "icon_id" => null,
                "characteristic_type" => "comparison_total_walk",
                "order" => null,
                "icon" => null,
                "characteristics" => [],
                "translables" => [
                    [
                        "lenguage_id" => "1",
                        "name" => "Caminata total",
                        "description" => "0"
                    ],
                    [
                        "lenguage_id" => "2",
                        "name" => "Caminata total",
                        "description" => "0"
                    ],
                    [
                        "lenguage_id" => "3",
                        "name" => "Caminata total",
                        "description" => "0"
                    ]
                ]
            ];
        // 29 comparison_waterfall_view
            $characteristics['characteristics'][] = [
                "icon_id" => null,
                "characteristic_type" => "comparison_waterfall_view",
                "order" => null,
                "icon" => null,
                "characteristics" => [],
                "translables" => [
                    [
                        "lenguage_id" => "1",
                        "name" => "Visita a Cascada",
                        "description" => "0"
                    ],
                    [
                        "lenguage_id" => "2",
                        "name" => "Visita a Cascada",
                        "description" => "0"
                    ],
                    [
                        "lenguage_id" => "3",
                        "name" => "Visita a Cascada",
                        "description" => "0"
                    ]
                ]
            ];
        return $characteristics;
    }
}
