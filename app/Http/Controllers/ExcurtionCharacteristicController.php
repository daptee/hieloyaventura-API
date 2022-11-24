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
use Illuminate\Support\Facades\DB;

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
                // return $this->bigIce();
                break;
            case 2:
                return $this->bigIce();
                break;
            case 3:
                // return $this->();
                break;
            case 4:
                return $this->safariAzul();
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
                            "name" =>  "Característica de la actividad",
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
                                    "description" =>  '<p>Aproximadamente 12 horas (Día completo)</p>'
                                ],
                                [
                                    # INGLES
                                    "lenguage_id" =>  "2",
                                    "description" =>  "<p>Approximately 12 hours (Full day)</p>"
                                ],
                                [
                                    # PORTUGUÉS
                                    "lenguage_id" =>  "3",
                                    "description" =>  "<p>Aproximadamente 12 horas (Dia inteiro)</p>"
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
                                    "description" =>  "<p>Desde el 15 de septiembre hasta el 30 de abril</p>"

                                ],
                                [
                                # INGLES
                                    "lenguage_id" =>  "2",
                                    "description" =>  "<p>From September 15th to April 30th.</p>"
                                ],
                                [
                                # PORTUGUÉS
                                    "lenguage_id" =>  "3",
                                    "description" =>  "<p>A partir de 15 de Setembro até 30 de abril</p>"
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
                                    "description" =>  '<p>Opcional traslado con guía y visita de una hora aproximadamente a pasarelas.</p>'

                                ],
                                [
                                # INGLES
                                    "lenguage_id" =>  "2",
                                    "description" =>  "<p>Optional transfer with guide, including a visit of about one hour to the walkways.</p>"
                                ],
                                [
                                # PORTUGUÉS
                                    "lenguage_id" =>  "3",
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
                                    "description" =>  "<p>Nuestros guías hablan español e inglés.</p>"

                                ],
                                [
                                # INGLES
                                    "lenguage_id" =>  "2",
                                    "description" =>  "<p>Our guides speak Spanish and English.</p>"
                                ],
                                [
                                # PORTUGUÉS
                                    "lenguage_id" =>  "3",
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
                                    "description" =>  '<p>Solo apto para <span style="color: #366895;">personas de 18 a 50 años.</span> Sin exepción.</p>'

                                ],
                                [
                                # INGLES
                                    "lenguage_id" =>  "2",
                                    "description" =>  '<p>Suitable for <span style="color: #366895;">people between 18 and 50 years ONLY.</span> No exceptions.</p>'
                                ],
                                [
                                # PORTUGUÉS
                                    "lenguage_id" =>  "3",
                                    "description" =>  '<p>Somente apto para <span style="color: #366895;">pessoas entre 18 e 50 anos.</span> Sem exceções.</p>'
                                ]
                            ]
                        ],
                    #$complexity
                        [
                            "icon" =>  '$complexity',
                            "order" =>  "6",
                            "translables" =>  [
                                [
                                #ESPAÑOL
                                    "lenguage_id" =>  "1",
                                    "description" =>  "<p>ALTA. Para que tengas una excelente experiencia en el Glaciar debés tener la capacidad psicofísica suficiente para caminar al menos 7 horas y media, siendo parte del trayecto sobre el hielo con crampones.</p>"
                                ],
                                [
                                # INGLES
                                    "lenguage_id" =>  "2",
                                    "description" =>  "<p>HIGH. In order to have a great experience on the Glacier, you should have the psychophysical capacity required to walk for at least 7 hours and a half, partly on ice and with crampons.</p>"
                                ],
                                [
                                # PORTUGUÉS
                                    "lenguage_id" =>  "3",
                                    "description" =>  "<p>ALTA. Para que você tenha uma experiência excelente no Glaciar, é imprescindível contar com capacidade psicofísica suficiente para caminhar, pelos menos, 7 horas e meia, uma parte do percorrido sobre gelo e com grampos.</p>"
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
                            <p><span style="color: #3686c3;"><strong>El Big Ice se realiza en un ambiente natural por lo cual las condiciones climáticas y características del glaciar y sus alrededores cambian diariamente. ¡Esto nos permite disfrutar de experiencias irrepetibles en el glaciar más lindo del mundo! ¡Los esperamos!</strong></span></p>
                            <p style="text-align: justify;">&nbsp;</p>'
                        ],
                        [
                            "lenguage_id" => 2,
                            "name" => "Sobre esta experiencia",
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
                            "name" => "Sobre esta experiencia",
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
                                        "description" => "<li>Personas con obesidad. <span style='font-size: 12px; color: #2471b9;'><a href='https://hieloyaventura.com/faq-es/' target='_blank' rel='noopener'><strong class='pum-trigger' style='cursor: pointer;'>Mas info.</strong></a></span></li>",
                                    ],
                                    [
                                        "lenguage_id" => 2,
                                        "name" => null,
                                        "description" => '<li>Obese persons.&nbsp;<span style="font-size: 12px; color: #2471b9;"><a href="https://hieloyaventura.com/faq-en/" target="_blank" rel="noopener"><strong class="pum-trigger" style="cursor: pointer;">More info.</strong></a></span></li>',
                                    ],
                                    [
                                        "lenguage_id" => 3,
                                        "name" => null,
                                        "description" => '<li>Pessoas obesas <a href="https://hieloyaventura.com/faq-es/"><strong class="pum-trigger" style="cursor: pointer;">Veja mais</strong></a></li>'
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
                                        "description" => "<li>Mujeres embarazadas.</li>",
                                    ],
                                    [
                                        "lenguage_id" => 2,
                                        "name" => null,
                                        "description" => "<li>Pregnant women.</li>",
                                    ],
                                    [
                                        "lenguage_id" => 3,
                                        "name" => null,
                                        "description" => "<li>Mulheres grávidas.</li>",
                                    ]
                                ]
                            ],
                            [
                                "icon_id" => null,
                                "order" => null,
                                "icon" => '$brain',

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
                                "icon" => '$heart_rate',

                                "characteristics" => [],
                                "translables" => [
                                    [
                                        "lenguage_id" => 1,
                                        "name" => null,
                                        "description" => "<li>Personas que sufran enfermedades cardiovasculares centrales o periféricas, que sus capacidades cardíacas o vasculares se encuentren disminuidas, o utilicen stent, bypass, marcapasos u otras prótesis. Ejemplo: medicamentos anticoagulantes, varices grado III (las que se evidencian gruesas y múltiples).</li>",
                                    ],
                                    [
                                        "lenguage_id" => 2,
                                        "name" => null,
                                        "description" => "<li>People who suffer from central or peripheral heart or vascular diseases, whose heart or vascular capabilities are limited, or people with stents, bypass, pacemaker or other prosthesis. Example: anticoagulant medication, stage 3 varicose veins (multiple thick varicose veins that can be noticed).</li>",
                                    ],
                                    [
                                        "lenguage_id" => 3,
                                        "name" => null,
                                        "description" => "<li>Pessoas com doenças cardiovasculares centrais ou periféricas, com capacidades cardíacas ou vasculares deficientes, ou quando utilizem stent, bypass, marca-passos ou outro tipo de prótese. Exemplo: medicamentos anti-coagulantes, varizes grau III (são grossas e múltiplas).Pessoas com doenças cardiovasculares centrais ou periféricas, com capacidades cardíacas ou vasculares deficientes, ou quando utilizem stent, bypass, marca-passos ou outro tipo de prótese. Exemplo: medicamentos anti-coagulantes, varizes grau III (são grossas e múltiplas).</li>",
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
                                        "description" => "<li>Personas que padezcan enfermedades provocadas de discapacidades respiratorias (EPOC, asma, enfisema, etc.)</li>",
                                    ],
                                    [
                                        "lenguage_id" => 2,
                                        "name" => null,
                                        "description" => "<li>People who suffer from diseases causing respiratory impairment (COPD, asthma, emphysema, etc.).</li>",
                                    ],
                                    [
                                        "lenguage_id" => 3,
                                        "name" => null,
                                        "description" => "<li>Pessoas com doenças que provoquem deficiências respiratórias (EPOC, asma, enfisema, etc.).</li>",
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

        //5 itinerary ////traducir todas estás características
            $characteristics['characteristics'][] = [
                "icon_id" => null,
                "characteristic_type" => 'itinerary',
                "order" => null,

                "characteristics" => [ //traducir todas estás características
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
                                        "description" => "80km de lagos, estepa y bosques."
                                    ],
                                    [
                                        "lenguage_id" => 2,
                                        "name" => "Salida de El Calafate",
                                        "description" => "80km de lagos, estepa y bosques."
                                    ],
                                    [
                                        "lenguage_id" => 3,
                                        "name" => "Salida de El Calafate",
                                        "description" => "80km de lagos, estepa y bosques."
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
                                        "description" => "20 minutos de navegación frente al Glaciar."
                                    ],
                                    [
                                        "lenguage_id" => 2,
                                        "name" => "Embarque en Puerto",
                                        "description" => "20 minutos de navegación frente al Glaciar."
                                    ],
                                    [
                                        "lenguage_id" => 3,
                                        "name" => "Embarque en Puerto",
                                        "description" => "20 minutos de navegación frente al Glaciar."
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
                                        "name" => "Trekking sobre el glaciar",
                                        "description" => "Caminata con crampones de aproximadamente 3 horas."
                                    ],
                                    [
                                        "lenguage_id" => 2,
                                        "name" => "Trekking sobre el glaciar",
                                        "description" => "Caminata con crampones de aproximadamente 3 horas."
                                    ],
                                    [
                                        "lenguage_id" => 3,
                                        "name" => "Trekking sobre el glaciar",
                                        "description" => "Caminata con crampones de aproximadamente 3 horas."
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
                                        "description" => "2 horas de caminata bordeando el Glaciar y 20 minurtos de navegación."
                                    ],
                                    [
                                        "lenguage_id" => 2,
                                        "name" => "Regreso al Puerto",
                                        "description" => "2 horas de caminata bordeando el Glaciar y 20 minurtos de navegación."
                                    ],
                                    [
                                        "lenguage_id" => 3,
                                        "name" => "Regreso al Puerto",
                                        "description" => "2 horas de caminata bordeando el Glaciar y 20 minurtos de navegación."
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
                                        "description" => "1 hora de vista panorámica del Glaciar Perito Moreno."
                                    ],
                                    [
                                        "lenguage_id" => 2,
                                        "name" => "Visita a Pasarelas",
                                        "description" => "1 hora de vista panorámica del Glaciar Perito Moreno."
                                    ],
                                    [
                                        "lenguage_id" => 3,
                                        "name" => "Visita a Pasarelas",
                                        "description" => "1 hora de vista panorámica del Glaciar Perito Moreno."
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
                                        "description" => ""
                                    ],
                                    [
                                        "lenguage_id" => 2,
                                        "name" => "Regreso a El Calafate",
                                        "description" => ""
                                    ],
                                    [
                                        "lenguage_id" => 3,
                                        "name" => "Regreso a El Calafate",
                                        "description" => ""
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
                                        "name" => "OPCIONAL CON TRASLADO",
                                        "description" => null
                                    ],
                                    [
                                        "lenguage_id" => 3,
                                        "name" => "OPCIONAL CON TRASLADO",
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
                                    "description" => "<p>Vestir ropa cómoda y abrigada. Campera y pantalón impermeable, calzado deportivo o botas de trekking impermeables. El clima es cambiante y hay que estar preparado para no mojarse ni pasar frío. Lentes de sol, protector solar, guantes, gorro.</p>"
                                ],
                                [
                                    "lenguage_id" => 3,
                                    "name" => null,
                                    "description" => "<p>Vestir ropa cómoda y abrigada. Campera y pantalón impermeable, calzado deportivo o botas de trekking impermeables. El clima es cambiante y hay que estar preparado para no mojarse ni pasar frío. Lentes de sol, protector solar, guantes, gorro.</p>"
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
                                    "description" => "<p>Llevar comida y bebida para el día. La Empresa no cuenta con servicio de venta de comidas ni bebidas.</p>"
                                ],
                                [
                                    "lenguage_id" => 3,
                                    "name" => null,
                                    "description" => "<p>Llevar comida y bebida para el día. La Empresa no cuenta con servicio de venta de comidas ni bebidas.</p>"
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
                                    "description" => "<p>Deberás presentar tu entrada al Parque Nacional. Podés comprarla <span class='text-primary'>acá (Seleccionar: 'Acceso Corredor Rio Mitre y Glaciar Perito Moreno')</span> o abonarla en efectivo (en pesos argentinos) al llegar al Parque Nacional.</p>"
                                ],
                                [
                                    "lenguage_id" => 3,
                                    "name" => null,
                                    "description" => "<p>Deberás presentar tu entrada al Parque Nacional. Podés comprarla <span class='text-primary'>acá (Seleccionar: 'Acceso Corredor Rio Mitre y Glaciar Perito Moreno')</span> o abonarla en efectivo (en pesos argentinos) al llegar al Parque Nacional.</p>"
                                ]
                            ]
                        ]
                    ],
                    "translables" => [
                        [
                            "lenguage_id" => 1,
                            "name" => "Que llevar en la excursión",
                            "description" => null
                        ],
                        [
                            "lenguage_id" => 2,
                            "name" => "Que llevar en la excursión",
                            "description" => null
                        ],
                        [
                            "lenguage_id" => 3,
                            "name" => "Que llevar en la excursión",
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
                        "description" => "<p>Debido al grado de esfuerzo y dificultad que esta actividad presenta y con el solo objetivo de preservar la salud, no podrán participar de la excursión ciertas personas.</p>"
                    ],
                    [
                        "lenguage_id" => 2,
                        "name" => "Restricciones importantes antes de comprar",
                        "description" => "<p></p>"//traducirlo
                    ],
                    [
                        "lenguage_id" => 3,
                        "name" => "Restricciones importantes antes de comprar",
                        "description" => "<p></p>" // traducirlo
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
                        "name" => "Navega frente al Glaciar Perito Moreno",
                        "description" => "1"
                    ],
                    [
                        "lenguage_id" => "3",
                        "name" => "Navega frente al Glaciar Perito Moreno",
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
                        "name" => "Trekking sobre hielo",
                        "description" => "3 horas"
                    ],
                    [
                        "lenguage_id" => "3",
                        "name" => "Trekking sobre hielo",
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
                        "name" => "Dificultad",
                        "description" => "Alta"
                    ],
                    [
                        "lenguage_id" => "3",
                        "name" => "Dificultad",
                        "description" => "Alta"
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
                        "lenguage_id" => "2",
                        "name" => "Vista de grietas",
                        "description" => "1"
                    ],
                    [
                        "lenguage_id" => "2",
                        "name" => "Vista de grietas",
                        "description" => "1"
                    ],
                    [
                        "lenguage_id" => "3",
                        "name" => "Vista de grietas",
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
                        "name" => "Vista de Seracs",
                        "description" => "1"
                    ],
                    [
                        "lenguage_id" => "3",
                        "name" => "Vista de Seracs",
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
                        "name" => "Vista de sumideros",
                        "description" => "1"
                    ],
                    [
                        "lenguage_id" => "3",
                        "name" => "Vista de sumideros",
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
                        "name" => "Vista de cuevas",
                        "description" => "eventualmente"
                    ],
                    [
                        "lenguage_id" => "3",
                        "name" => "Vista de cuevas",
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
                        "name" => "Vista de lagunas",
                        "description" => "1"
                    ],
                    [
                        "lenguage_id" => "3",
                        "name" => "Vista de lagunas",
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
                        "description" => "10"
                    ],
                    [
                        "lenguage_id" => "2",
                        "name" => "Tamaño de grupo",
                        "description" => "10"
                    ],
                    [
                        "lenguage_id" => "3",
                        "name" => "Tamaño de grupo",
                        "description" => "10"
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
                        "name" => "Trekking por costa del lago",
                        "description" => "0"
                    ],
                    [
                        "lenguage_id" => "3",
                        "name" => "Trekking por costa del lago",
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
                        "name" => "Trekking por bosque",
                        "description" => "1"
                    ],
                    [
                        "lenguage_id" => "3",
                        "name" => "Trekking por bosque",
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
                        "name" => "Comida incluida",
                        "description" => "0"
                    ],
                    [
                        "lenguage_id" => "3",
                        "name" => "Comida incluida",
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
                        "name" => "Traslado desde el hotel",
                        "description" => "optativo"
                    ],
                    [
                        "lenguage_id" => "3",
                        "name" => "Traslado desde el hotel",
                        "description" => "optativo"
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
                        "description" => null
                    ],
                    [
                        "lenguage_id" => "2",
                        "name" => "Precio actual",
                        "description" => null
                    ],
                    [
                        "lenguage_id" => "3",
                        "name" => "Precio actual",
                        "description" => null
                    ]
                ]
            ];




        //

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
                            "name" =>  "Característica de la actividad",
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
                                "name"        =>  "Duración CON traslado y pasarelas",
                                "description" =>  '<p>Aproximadamente 8 horas (Día completo)</p>'
                            ],
                            [
                                # INGLES
                                "lenguage_id" =>  "2",
                                "name"        =>  "Duration WITH transfer and walkways",  
                                "description" =>  "<p>Approximately 8 hours (Full day)</p>"
                            ],
                            [
                                # PORTUGUÉS
                                "lenguage_id" =>  "3",
                                "name"        =>  "Duração COM traslado e passarelas",  
                                "description" =>  "<p>Aproximadamente 8 horas (Dia completo)</p>"
                            ]
                        ]
                    ],
                    #$clockSinTraslado
                    [
                        "icon" =>  '$clock',
                        "order" =>  "1",
                        "translables" =>  [
                            [
                                #ESPAÑOL
                                "lenguage_id" =>  "1",
                                "name"        =>  "Duración SIN traslado y pasarelas",
                                "description" =>  '<p>Aproximadamente 2.45 horas</p>'
                            ],
                            [
                                # INGLES
                                "lenguage_id" =>  "2",
                                "name"        =>  "Duration WITHOUT transfer and walkways",
                                "description" =>  "<p>Approximately 12 hours (Full day)</p>"
                            ],
                            [
                                # PORTUGUÉS
                                "lenguage_id" =>  "3",
                                "name"        =>  "Duração SEM traslado e passarelas",
                                "description" =>  "<p>Aproximadamente 12 horas (Dia inteiro)</p>"
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
                                    "description" =>  "<p>Desde el 15 de Julio al 31 de Mayo</p>"

                                ],
                                [
                                # INGLES
                                    "lenguage_id" =>  "2",
                                    "description" =>  "<p>From September 15th to April 31th.</p>"
                                ],
                                [
                                # PORTUGUÉS
                                    "lenguage_id" =>  "3",
                                    "description" =>  "<p>A partir de 15 de Setembro até 31 de abril</p>"
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
                                    "description" =>  '<p>Opcional traslado con guía y visita de aproximadamente dos horas a pasarelas.</p>'

                                ],
                                [
                                # INGLES
                                    "lenguage_id" =>  "2",
                                    "description" =>  "<p>Optional transfer with a guide and an approx. 2-hour visit to the walkways.</p>"
                                ],
                                [
                                # PORTUGUÉS
                                    "lenguage_id" =>  "3",
                                    "description" =>  "<p>Traslado opcional, com guia e visita de aproximadamente duas horas às passarelas.</p>"
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
                                    "description" =>  "<p>Nuestros guías hablan español e inglés.</p>"

                                ],
                                [
                                # INGLES
                                    "lenguage_id" =>  "2",
                                    "description" =>  "<p>Our guides can speak Spanish and English.</p>"
                                ],
                                [
                                # PORTUGUÉS
                                    "lenguage_id" =>  "3",
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
                                    "description" =>  '<p>Solo apto para <span style="color: #366895;">personas de 6 a 70 años.</span></p>'

                                ],
                                [
                                # INGLES
                                    "lenguage_id" =>  "2",
                                    "description" =>  '<p>Only suitable for <span style="color: #366895;">people between 6 and 70 years</span></p>'
                                ],
                                [
                                # PORTUGUÉS
                                    "lenguage_id" =>  "3",
                                    "description" =>  '<p>Apto somente para <span style="color: #366895;">pessoas entre 6 e 70 anos.</span></p>'
                                ]
                            ]
                        ],
                    #$complexity
                        [
                            "icon" =>  '$complexity',
                            "order" =>  "6",
                            "translables" =>  [
                                [
                                #ESPAÑOL
                                    "lenguage_id" =>  "1",
                                    "description" =>  "<p>Si bien la intensidad es baja el terreno presenta piedras, pendientes suaves y escaleras.</p>"
                                ],
                                [
                                # INGLES
                                    "lenguage_id" =>  "2",
                                    "description" =>  "<p>Even though the difficulty of the tour is low, the surface has stones, gentle slopes and stairs.</p>"
                                ],
                                [
                                # PORTUGUÉS
                                    "lenguage_id" =>  "3",
                                    "description" =>  "<p>Embora a dificuldade seja baixa, o terreno tem pedras, declives e escadas.</p>"
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
                            "name" => "About",
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
                            <p><span style="color: #3686c3;"><strong>El Safari Azul se realiza en un ambiente natural por lo cual las condiciones climáticas y características del glaciar y sus alrededores cambian diariamente.</strong></span> <span style="color: #3686c3;">Sin embargo, <strong>la excursión no se suspende</strong>, mientras que las condiciones de seguridad lo permitan.</span></p>
                            <p style="text-align: justify;">&nbsp;</p>'
                        ],
                        [
                            "lenguage_id" => 2,
                            "name" => "Sobre esta experiencia",
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
                            "name" => "Sobre esta experiencia",
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
                "translables" => [
                    [
                        "lenguage_id" => 1,
                        "name" => "A TENER EN CUENTA ANTES DE COMPRAR",
                        "description" => '<p><span style="color: rgb(36, 113, 185);"><strong>Aclaraci&oacute;n:</strong></span> Esta excursi&oacute;n NO incluye caminata sobre el Glaciar Perito Moreno.</p>
                        <p style="text-align: justify;">&nbsp;</p>
                        <p><strong><span style="color: rgb(36, 113, 185);">No incluye:</span></strong>&nbsp;Entrada al Parque Nacional | Comida y bebida | Ropa personal adecuada a las condiciones clim&aacute;ticas de la regi&oacute;n. (fr&iacute;o, lluvia, viento, nieve).</p>
                        <p style="text-align: justify;">&nbsp;</p>
                        <p><span style="color: rgb(70, 127, 231);"><strong><a style="color: rgb(70, 127, 231);" href="https://hieloyaventura.com/terminos-y-condiciones/" target="_blank" rel="noopener">T&eacute;rminos y Condiciones.</a>&nbsp;|&nbsp;<a style="color: rgb(70, 127, 231);" href="https://hieloyaventura.com/politicas-de-cancelacion/" target="_blank" rel="noopener">Pol&iacute;ticas de cancelaci&oacute;n.</a></strong></span></p>
                        <p style="text-align: justify;">&nbsp;</p>'
                    ],
                    [
                        "lenguage_id" => 2,
                        "name" => "BEFORE PURCHASING YOUR TICKETS, PLEASE KEEP IN MIND THE FOLLOWING:",
                        "description" => '<p><span style="color: rgb(36, 113, 185);"><strong>Important:</strong></span>&nbsp;This tour doesn&rsquo;t include walking on the Perito Moreno Glacier.</p>
                        <p style="text-align: justify;">&nbsp;</p>
                        <p><strong><span style="color: rgb(36, 113, 185);">Not included:</span></strong>&nbsp;Ticket to the National Park | Food and drink. | Personal clothes suitable for the weather conditions of the place. (cold, rain, wind, snow) </p>
                        <p style="text-align: justify;">&nbsp;</p>
                        <p><span style="color: rgb(70, 127, 231);"><a style="color: rgb(70, 127, 231);" href="https://hieloyaventura/en/terms-and-conditions/" target="_blank" rel="noopener"><strong>Terms and conditions.</strong></a>&nbsp;|&nbsp;</span><a href="https://hieloyaventura/en/Cancellation-policy/" target="_blank" rel="noopener"><strong><span style="color: rgb(70, 127, 231);">Cancellation policy.</span></strong></a></p>
                        <p style="text-align: justify;">&nbsp;</p>'
                    ],
                    [
                        "lenguage_id" => 3,
                        "name" => "LEVAR EM CONTA ANTES DE COMPRAR",
                        "description" => '<p><span style="color: rgb(36, 113, 185);"><strong>Esclarecimento:</strong></span>&nbsp; A excurs&atilde;o N&Atilde;O INCLUI caminhada sobre o Glaciar Perito Moreno.</p>
                        <p style="text-align: justify;">&nbsp;</p>
                        <p><span style="color: rgb(36, 113, 185);"><strong>N&atilde;o&nbsp;inclui:</strong></span>&nbsp; Ingresso ao Parque Nacional | Comida e bebida | Vestimenta pessoal adequada para as condi&ccedil;&otilde;es clim&aacute;ticas pr&oacute;prias da regi&atilde;o (frio, chuva, vento, neve).</p>
                        <p style="text-align: justify;">&nbsp;</p>
                        <p><span style="color: rgb(70, 127, 231);"><a style="color: rgb(70, 127, 231);" href="https://hieloyaventura.com/pt/termos-e-condicoes/" target="_blank" rel="noopener"><strong>Termos e Condi&ccedil;&otilde;es.&nbsp;</strong></a>|&nbsp;<a style="color: rgb(70, 127, 231);" href="https://hieloyaventura.com/pt/politicas-de-cancelamento/" target="_blank" rel="noopener"><strong>Pol&iacute;ticas de cancelamento.</strong></a></span></p>
                        <p>&nbsp;</p>
                        <p style="text-align: justify;">&nbsp;</p>'
                    ]
                ]
            ];

        //5 itinerary ////traducir todas estás características
            $characteristics['characteristics'][] = [
                "icon_id" => null,
                "characteristic_type" => 'itinerary',
                "order" => null,

                "characteristics" => [ //traducir todas estás características
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
                                        "description" => "20 minutos de navegación"
                                    ],
                                    [
                                        "lenguage_id" => 2,
                                        "name" => "Embarking at the Port",
                                        "description" => "20-minute navigation"
                                    ],
                                    [
                                        "lenguage_id" => 3,
                                        "name" => "Embarque no Porto",
                                        "description" => "20 minutos de navegação"
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
                                "icon" => '$stairs',
                                "characteristics" => [],
                                "translables" => [
                                    [
                                        "lenguage_id" => 1,
                                        "name" => "Visita a Pasarelas",
                                        "description" => null
                                    ],
                                    [
                                        "lenguage_id" => 2,
                                        "name" => "Visiting the Walkways",
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
                                        "name" => "Opcional de traslado",
                                        "description" => null
                                    ],
                                    [
                                        "lenguage_id" => 2,
                                        "name" => "Optional transfer service",
                                        "description" => null
                                    ],
                                    [
                                        "lenguage_id" => 3,
                                        "name" => "Opcional de traslado",
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
                                        "name" => "Tour incluido",
                                        "description" => null
                                    ],
                                    [
                                        "lenguage_id" => 2,
                                        "name" => "Tour included",
                                        "description" => null
                                    ],
                                    [
                                        "lenguage_id" => 3,
                                        "name" => "Passeio Incluído",
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
                        <p><span style="color: rgb(70, 127, 231);"><strong><a style="color: rgb(70, 127, 231);" href="https://hieloyaventura.com/terminos-y-condiciones/" target="_blank" rel="noopener">T&eacute;rminos y Condiciones.</a>&nbsp;|&nbsp;<a style="color: rgb(70, 127, 231);" href="https://hieloyaventura.com/politicas-de-cancelacion/" target="_blank" rel="noopener">Pol&iacute;ticas de cancelaci&oacute;n.</a></strong></span></p>
                        <p style="text-align: justify;">&nbsp;</p>'
                    ],
                    [
                        "lenguage_id" => 2,
                        "name" => "Restricciones importantes antes de comprar",
                        "description" => '<p><span style="color: rgb(36, 113, 185);"><strong>Important:</strong></span>&nbsp;This tour doesn&rsquo;t include walking on the Perito Moreno Glacier.</p>
                        <p style="text-align: justify;">&nbsp;</p>
                        <p><strong><span style="color: rgb(36, 113, 185);">Not included:</span></strong>&nbsp;Ticket to the National Park | Food and drink. | Personal clothes suitable for the weather conditions of the place. (cold, rain, wind, snow) </p>
                        <p style="text-align: justify;">&nbsp;</p>
                        <p><span style="color: rgb(70, 127, 231);"><a style="color: rgb(70, 127, 231);" href="https://hieloyaventura/en/terms-and-conditions/" target="_blank" rel="noopener"><strong>Terms and conditions.</strong></a>&nbsp;|&nbsp;</span><a href="https://hieloyaventura/en/Cancellation-policy/" target="_blank" rel="noopener"><strong><span style="color: rgb(70, 127, 231);">Cancellation policy.</span></strong></a></p>
                        <p style="text-align: justify;">&nbsp;</p>'
                    ],
                    [
                        "lenguage_id" => 3,
                        "name" => "Restricciones importantes antes de comprar",
                        "description" => '<p><span style="color: rgb(36, 113, 185);"><strong>Esclarecimento:</strong></span>&nbsp; A excurs&atilde;o N&Atilde;O INCLUI caminhada sobre o Glaciar Perito Moreno.</p>
                        <p style="text-align: justify;">&nbsp;</p>
                        <p><span style="color: rgb(36, 113, 185);"><strong>N&atilde;o&nbsp;inclui:</strong></span>&nbsp; Ingresso ao Parque Nacional | Comida e bebida | Vestimenta pessoal adequada para as condi&ccedil;&otilde;es clim&aacute;ticas pr&oacute;prias da regi&atilde;o (frio, chuva, vento, neve).</p>
                        <p style="text-align: justify;">&nbsp;</p>
                        <p><span style="color: rgb(70, 127, 231);"><a style="color: rgb(70, 127, 231);" href="https://hieloyaventura.com/pt/termos-e-condicoes/" target="_blank" rel="noopener"><strong>Termos e Condi&ccedil;&otilde;es.&nbsp;</strong></a>|&nbsp;<a style="color: rgb(70, 127, 231);" href="https://hieloyaventura.com/pt/politicas-de-cancelamento/" target="_blank" rel="noopener"><strong>Pol&iacute;ticas de cancelamento.</strong></a></span></p>
                        <p>&nbsp;</p>
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
                        "name" => "view of caves",
                        "description" => "eventualmente"
                    ],
                    [
                        "lenguage_id" => "3",
                        "name" => "vista das cavernas",
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
                        "description" => "10"
                    ],
                    [
                        "lenguage_id" => "2",
                        "name" => "group size",
                        "description" => "10"
                    ],
                    [
                        "lenguage_id" => "3",
                        "name" => "Tamanho do grupo",
                        "description" => "10"
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
                        "name" => "trekking through forest",
                        "description" => "1"
                    ],
                    [
                        "lenguage_id" => "3",
                        "name" => "trekking pela floresta",
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
                        "description" => "optativo"
                    ],
                    [
                        "lenguage_id" => "3",
                        "name" => "Traslado do hotel",
                        "description" => "optativo"
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
                        "description" => null
                    ],
                    [
                        "lenguage_id" => "2",
                        "name" => "Actual Price",
                        "description" => null
                    ],
                    [
                        "lenguage_id" => "3",
                        "name" => "Preço real",
                        "description" => null
                    ]
                ]
            ];




        //

        return $characteristics;
    }
}
