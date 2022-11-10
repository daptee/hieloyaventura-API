<?php

namespace App\Http\Controllers;

use App\Helpers\UploadFileHelper;
use App\Http\Requests\StoreExcurtionCharacteristicRequest;
use App\Http\Requests\UpdateExcurtionCharacteristicRequest;
use App\Models\Characteristic;
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
        $datos = $this->datos();

        // return $datos['characteristics'][0];
        $excurtion = Excurtion::findOrFail($id);

        
        try {
            DB::beginTransaction();

            // return $excurtion->characteristics2->pluck('id');
            
            // foreach ($excurtion->characteristics2 as $characteristic) {
            //     $this->deleteCharacteristics($characteristic);
            // }

            $excurtion->characteristics2()->detach();

                foreach ($datos['characteristics'] as $characteristic) {
                    Characteristic::addCharacteristic($characteristic, $excurtion->id, null);
                }
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

    private function datos()
    {
        $characteristics = [];

        //1 characteristics //Traducir de acá para abajo
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
                                    "description" =>  ""
                                ],
                                [
                                    # PORTUGUÉS
                                    "lenguage_id" =>  "3",
                                    "description" =>  ""
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
                                    "description" =>  ""
                                ],
                                [
                                # PORTUGUÉS
                                    "lenguage_id" =>  "3",
                                    "description" =>  ""
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
                                    "description" =>  ""
                                ],
                                [
                                # PORTUGUÉS
                                    "lenguage_id" =>  "3",
                                    "description" =>  ""
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
                                    "description" =>  ""
                                ],
                                [
                                # PORTUGUÉS
                                    "lenguage_id" =>  "3",
                                    "description" =>  ""
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
                                    "description" =>  ""
                                ],
                                [
                                # PORTUGUÉS
                                    "lenguage_id" =>  "3",
                                    "description" =>  ""
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
                                    "description" =>  ""
                                ],
                                [
                                # PORTUGUÉS
                                    "lenguage_id" =>  "3",
                                    "description" =>  ""
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
                            "description" => "<p>El Big Ice es una excursión de día completo que comienza con la búsqueda de los pasajeros en El Calafate. En nuestros confortables buses, camino al Parque Nacional Los Glaciares, los guías de turismo les brindarán información sobre la actividad, el lugar y el glaciar.</p>
                            <p><span style='color: #3686c3;'><strong>Una vez en el puerto “Bajo de las Sombras” (Ruta 11, a 70 km de El Calafate)</strong> <strong>embarcarán para cruzar el Lago Rico,</strong></span> llegando a la costa opuesta luego de aproximadamente 20 minutos de navegación frente a la imponente cara sur del Glaciar Perito Moreno.</p>
                            <p>Al llegar al refugio el grupo será recibido por expertos guías de montaña, quienes los dividirán en subgrupos y los acompañarán durante todo el recorrido. El trekking <span style='color: #3686c3;'><strong>comienza con una caminata por la morrena de aproximadamente 2 horas, </strong></span>donde se podrán observar diferentes vistas panorámicas del glaciar y del bosque.</p>
                            <p><strong><span style='color: #3686c3;'>El Big Ice es una excursión altamente personalizada:</span>&nbsp; </strong>los grupos sobre el hielo serán de hasta 10 personas, acompañados por dos guías de montaña quienes les colocarán los&nbsp;crampones, cascos y arneses&nbsp;&nbsp; y les explicarán las&nbsp;normas básicas de seguridad.</p>
                            <p><span style='color: #3686c3;'><strong>La exigencia física es alta tanto en el bosque como sobre el hielo, donde la superficie es irregular pero firme y segura. </strong></span></p>
                            <p>Una vez en el glaciar y con los crampones puestos, el mundo toma una nueva perspectiva:&nbsp;lagunas azules, profundas grietas, enormes sumideros, mágicas cuevas, y la sensación única de sentirse en el corazón del glaciar.</p>
                            <p><span style='color: #3686c3;'><strong>Explorarán durante tres horas aproximadamente los rincones del glaciar más especial del mundo.</strong></span> Durante el recorrido, los guías de montaña los ayudarán a conocer mejor el hielo, su entorno y podrán dimensionar la&nbsp;magnitud del glaciar&nbsp;y disfrutar de la vista de las montañas aledañas, como los cerros Dos Picos, Pietrobelli y Cervantes. Además, contarán con media hora para almorzar y sorprenderse en un lugar de inigualable belleza.</p>
                            <p>Al finalizar la caminata sobre el glaciar, emprenderán el regreso por el mismo camino hasta llegar al Refugio, donde tendrán unos minutos para contemplar este lugar de inigualable belleza. Al tomar la embarcación de regreso, navegarán muy cerca de&nbsp;la cara sur del Glaciar Perito Moreno&nbsp;para luego volver a la “civilización”, ¡después de haber disfrutado <span style='color: #3686c3;'><strong>uno de los treks sobre hielo más espectaculares del mundo!</strong></span></p>
                            <p><strong>&nbsp;</strong><strong><span style='color: #3686c3;'>La duración de la excursión con el traslado es de alrededor de doce horas en total</span>&nbsp;</strong>e incluye la visita guiada de una hora aproximadamente a las pasarelas del Glaciar Perito Moreno, a 7 km del puerto. Allí podrán disfrutar de la espectacular vista panorámica del glaciar y recorrer alguno de los senderos auto-guiados. En caso de no optar por nuestro transporte e ir por sus propios medios, el <span style='color: #3686c3;'><strong>Big Ice</strong></span> dura siete horas y media aproximadamente, saliendo desde el Puerto y regresando al mismo punto de partida.</p>
                            <p><span style='color: #3686c3;'><strong>El Big Ice se realiza en un ambiente natural por lo cual las condiciones climáticas y características del glaciar y sus alrededores cambian diariamente. ¡Esto nos permite disfrutar de experiencias irrepetibles en el glaciar más lindo del mundo! ¡Los esperamos!</strong></span></p>"
                        ],
                        [
                            "lenguage_id" => 2,
                            "name" => "Sobre esta experiencia",
                            "description" => '<p style="text-align: justify;">Big Ice is a full day tour, starting with passenger pick-up in El Calafate. On our way to Parque Nacional Los Glaciares, aboard our comfortable buses, our tour guides will give you information on the tour, the place and the glacier.</p>
                            <p style="text-align: justify;"><span style="color: #2471b9;"><strong>Once you arrive at “Bajo de las Sombras” port (located on Route 11, 70 Km from El Calafate)</strong>&nbsp;</span><strong><span style="color: #2471b9;">you will board a ship to cross Lago Rico</span>,</strong>&nbsp;and descend on the opposite coast after a 20-minute navigation in front of the stunning south face of Glaciar Perito Moreno.</p>
                            <p style="text-align: justify;">When the group gets to the shelter, it will be welcomed by expert mountain guides, who will divide it in subgroups and will stay with them throughout the walk. The trekking&nbsp;<strong><span style="color: #2471b9;">starts with a walk along the moraine for about 2 hours</span>,&nbsp;</strong>where you will be able to enjoy different panoramic views of the glacier and the woods.</p>
                            <p style="text-align: justify;"><span style="color: #2471b9;"><strong>Big Ice is a highly personalized tour:</strong></span><strong>&nbsp;&nbsp;</strong>For the ice walk, passengers will be divided into groups of up to 10 people, with 2 mountain guides who will fit the crampons, helmets and harnesses and will explain the basic safety rules.</p>
                            <p style="text-align: justify;"><span style="color: #2471b9;"><strong>A high level of physical effort is required in the woods as well as on the ice, where the surface is irregular, but firm and safe.</strong></span></p>
                            <p style="text-align: justify;">Once you are on the glacier and with the crampons on, the world seems different: blue ponds, deep cracks, huge moulins, magical caves and the unique feeling of being in the heart of the glacier.</p>
                            <p style="text-align: justify;"><span style="color: #2471b9;"><strong>During approximately three hours, you will explore every corner of the most special glacier in the world.</strong></span>&nbsp;During the walk, the mountain guides will help you learn more about the ice and its environment and you will be able to appreciate the dimensions of the glacier and enjoy the view of the surrounding mountains, such as Dos Picos, Pietrobelli and Cervantes hills. There will be a 30-minute break to have lunch while enjoying the amazing and unique beauty of the surroundings.</p>
                            <p style="text-align: justify;">At the end of the trekking on the glacier, you will go back to the shelter by the same path, where you will have some minutes to appreciate this site of unparalleled beauty. Then, you will board the ship back and you will navigate very close to the south face of Glaciar Perito Moreno and later return to the “civilization”, after having enjoyed&nbsp;<span style="color: #2471b9;"><strong>one of the most spectacular ice treks in the world!</strong></span></p>
                            <p style="text-align: justify;"><span style="color: #2471b9;"><strong>The duration of this tour is about 12 hours, including the transfer </strong></span>and a one-hour guided visit to the walkways of Glaciar Perito Moreno, 7 km from the port. There, you will enjoy the spectacular panoramic view of the glacier and walk along some of the self-guided paths. If you don’t use our transfer and go by your own means, the <span style="color: #2471b9;"><strong>Big Ice</strong></span>&nbsp;tour takes about seven hours and a half, leaving from the port and returning to the same point.</p>
                            <p style="text-align: justify;"><span style="color: #2471b9;"><strong>The Big Ice tour is carried out in a natural environment, so weather conditions and the glacier and its surroundings change every day. This allows you to enjoy unique experiences at the most beautiful glacier in the world! We are waiting for you!</strong></span></p>'
                        ],
                        [
                            "lenguage_id" => 3,
                            "name" => "Sobre esta experiencia",
                            "description" => '<p>O passeio começa no momento do <b style="color: #2471b9;">pick up</b>, cedo de manhã, no ponto de encontro acordado na cidade de El Calafate. Em nossos <b style="color: #2471b9;">confortáveis buses</b>, <b style="color: #2471b9;">um guia de turismo bilíngue</b> lhe oferecerá informações sobre a paisagem por descobrir.</p>
                            <p>Inclui visita guiada às <b style="color: #2471b9;">passarelas do Parque Nacional Los Glaciares</b>. Lá, você poderá desfrutar da espetacular paisagem panorâmica da geleira e percorrer algumas das trilhas autoguiadas.</p>
                            <p>Ao chegar o <b style="color: #2471b9;">porto “Bajo de las Sombras”</b>, localizado a apenas 7 km da geleira, você cruzará o Braço Rico em uma embarcação, para descer, depois de 20 minutos de navegação, no lado oposto.</p>
                            <p>Pequenos grupos de até <b style="color: #2471b9;">10 pessoas</b> são organizados para a caminhada, que começa pela morena sul da geleira. Em pouco mais de uma hora, chegam a um <b style="color: #2471b9;">ponto de observação espetacular</b> a partir do qual terão acesso ao gelo. Lá, os guias explicarão as normas básicas de segurança e ajustarão os <b style="color: #2471b9;">grampos, arreios e capacetes</b> necessários para iniciar a viagem.</p>
                            <p>Ao chegar à geleira, e com os grampos colocados, o mundo adquire uma nova perspectiva: <b style="color: #2471b9;">lagoas azuis, profundas gretas, enormes sumidouros,</b> mágicas cavernas e a sensação única de estar no coração da geleira.</p>
                            <p>Você sempre será acompanhado por nossos guias de montanha que, junto com você, explorarão por aproximadamente <b style="color: #2471b9;">três horas e meia</b> os cantos da geleira mais especial do mundo. No percorrido, com a ajuda dos guias, os grupos poderão conhecer melhor o gelo, seu entorno, assim como experimentar <b style="color: #2471b9;">a grandeza da geleira</b> e aproveitar da vista das montanhas ao redor, como o Cerro Dos Picos, o Cerro Pietrobelli e o Cerro Cervantes.&nbsp; Além disso, poderão desfrutar de meia hora para almoçar sobre o manto branco e se surpreender em um lugar de beleza incomparável.</p>
                            <p>Ao retornar à morena, os grupos caminharão mais uma hora até chegar ao barco de retorno, e navegarão muito próximo da <b style="color: #2471b9;">parede sul da Geleira Perito Moreno</b>. Os grupos retornarão à “civilização” depois de ter desfrutado de um dos passeios sobre gelo mais espetaculares do mundo!</p>
                            <p>O Big Ice é uma excursão de um dia completo que começa com a retirada dos passageiros na cidade de El Calafate. Em nossos confortáveis ônibus, caminho ao Parque Nacional Los Glaciares, os guias de turismo oferecerão informações sobre a atividade, a área e a geleira.</p>
                            <p><b style="color: #2471b9;">Ao chegar ao porto “Bajo de las Sombras” (Ruta 11, a 70 km de El Calafate), começa a navegação em barco, atravessando o Lago Rico</b> até atingir a costa oposta, logo após 20 minutos de navegação com vista para a parede sul do Glaciar Perito Moreno.</p>
                            <p>Ao chegar ao abrigo, o grupo será recebido por expertos guias de montanha que o dividirão em subgrupos e os acompanharão durante todo o percorrido. <b style="color: #2471b9;">O trekking começa com uma caminhada de aproximadamente 2 horas pela morena.</b> Lá, os passageiros poderão desfrutar de diferentes vistas panorâmicas da geleira e do bosque.</p>
                            <p><b style="color: #2471b9;">O Big Ice é uma excursão muito personalizada:</b>  Os grupos para caminhar sobre o gelo terão até 10 pessoas e serão acompanhadas por dois guias de montanha que colocarão os grampos, capacetes e arneses, e explicarão as normas básicas de segurança.</p>
                            <p><b style="color: #2471b9;">A exigência física é alta, tanto no bosque quanto sobre o gelo, onde a superfície é irregular, mas firme e segura.</b><br>
                            Ao chegar à geleira, e com os grampos colocados, o mundo adquire uma nova perspectiva: lagoas azuis, profundas fendas, enormes sumidouros, mágicas cavernas e a sensação única de estar no coração da geleira.</p>
                            <p><b style="color: #2471b9;">Os grupos explorarão e percorrerão, durante perto de três horas, a geleira mais bonita do mundo.</b> No percorrido, com a ajuda dos guias de montanha, os grupos poderão conhecer melhor o gelo, seu entorno, assim como experimentar a grandeza da geleira e desfrutar da vista das montanhas ao redor, como o cerro Dos Picos, o Cerro Pietrobelli e o Cerro Cervantes. Além disso, poderão desfrutar de meia hora para almoçar, e admirar um lugar de beleza incomparável.</p>
                            <p>Ao concluir a caminhada sobre a geleira, os grupos retornarão pelo mesmo caminho até o abrigo, onde terão alguns minutos para contemplar a beleza inigualável da área. Ao retornar à embarcação, os grupos navegarão muito próximo da parede sul do Glaciar Perito Moreno para retornar à “civilização” depois de ter desfrutado <b style="color: #2471b9;">um dos treks sobre gelo mais espetaculares do mundo!</b></p>
                            <p><b style="color: #2471b9;">A duração total da excursão mais o traslado é de aproximadamente doze horas</b> e inclui uma visita guiada de perto de uma hora às passarelas do Glaciar Perito Moreno, a 7 km do porto. Ali poderão desfrutar da espetacular vista panorâmica da geleira e percorrer algumas das trilhas autoguiadas. Se você não escolher nosso transporte e utilizar seus próprios meios, lembre-se que a duração do <b style="color: #2471b9;">Big Ice</b> é sete horas e meia aproximadamente, saindo do Porto e voltando para o mesmo ponto de saída.</p>
                            <p><b style="color: #2471b9;">O Big Ice é realizado em um ambiente natural e com condições climáticas e características da geleira e seu entorno que mudam todos os dias. Isso nos permite desfrutar de experiências irrepetíveis na geleira mais bonita do mundo! Esperamos vocês!</b></p>'
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
                                        "description" => "",
                                    ],
                                    [
                                        "lenguage_id" => 3,
                                        "name" => null,
                                        "description" => "",
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
                        "name" => "Itinerario de la excursión",
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
                                ]
                            ]
                        ]
                    ],
                    "translables" => [
                        [
                            "lenguage_id" => 1,
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
}
