<?php

namespace App\Console\Commands;

use Log;
use Carbon\Carbon;
use App\Models\User;
use App\Models\Tenant;
use App\Models\Tenant\Tasks;
use App\Models\Tenant\Pedidos;
use Illuminate\Console\Command;
use App\Events\Alerts\AlertEvent;
use App\Models\Tenant\TeamMember;
use Illuminate\Support\Facades\DB;
use App\Models\Tenant\Intervencoes;
use Illuminate\Support\Facades\Config;
use App\Models\Tenant\CustomerServices;
use App\Events\Alerts\EmailConclusionEvent;
use App\Events\Alerts\CheckFinalizadosEvent;
use App\Models\Tenant\ProdutosPHC;
use App\Models\Tenant\StampsClientes;
use Stancl\Tenancy\Controllers\TenantAssetsController;

class CheckStamps extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'alerts:check_stamps';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
       
        tenancy()->runForMultiple(null, function (Tenant $tenant) {
          
            $curl = curl_init();

            curl_setopt_array($curl, array(
                CURLOPT_URL => 'http://phc.brvr.pt:443/customers/customers',
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => '',
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 0,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => 'GET',
                CURLOPT_HTTPHEADER => array(
                    'Content-Type: application/json'
                ),
            ));
    
            $response = curl_exec($curl);
    
            curl_close($curl);
    
            $response_decoded = json_decode($response);

            foreach($response_decoded->customers as $decoded)
            {
               $cliente = StampsClientes::where('stamp',$decoded->id)->first();

               if(empty($cliente))
               {
                    StampsClientes::create([
                        "stamp" => $decoded->id,
                        "nome_cliente" => $decoded->name
                    ]);


                    $arrayPHCLocation = [
                        "name" =>  $decoded->name."|".$decoded->address,
                        "no" => $decoded->no,
                        "addressname" => $decoded->address,
                        "phone" => $decoded->phone,
                        "managername" => $decoded->name,
                        "locationmainornot" => true,
                        "phonemanager" => $decoded->phone,
                        "address" =>$decoded->address,
                        "zipcode" => $decoded->zipcode,
                        "state" => $decoded->state,
                        "longitude" => "",
                        "latitude" => "",
                        "city" => $decoded->city
                    ];
        
        
                    $encodedLocation = json_encode($arrayPHCLocation);
        
                    

                    $curlLocais = curl_init();

                    curl_setopt_array($curlLocais, array(
                        CURLOPT_URL => 'http://phc.brvr.pt:443/location/locations',
                        CURLOPT_RETURNTRANSFER => true,
                        CURLOPT_ENCODING => '',
                        CURLOPT_MAXREDIRS => 10,
                        CURLOPT_TIMEOUT => 0,
                        CURLOPT_FOLLOWLOCATION => true,
                        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                        CURLOPT_CUSTOMREQUEST => 'POST',
                        CURLOPT_POSTFIELDS => $encodedLocation,
                        CURLOPT_HTTPHEADER => array(
                            'Content-Type: application/json'
                        ),
                    ));
            
                    $responseLocais = curl_exec($curlLocais);
            
                    curl_close($curlLocais);


               }
            }


            /** PRODUTOS **/

            $curl = curl_init();

            curl_setopt_array($curl, array(
                CURLOPT_URL => 'http://phc.brvr.pt:443/products/products',
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => '',
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 0,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => 'GET',
                CURLOPT_HTTPHEADER => array(
                    'Content-Type: application/json'
                ),
            ));
    
            $response = curl_exec($curl);
    
            curl_close($curl);
    
            $response_decoded_prd = json_decode($response);

            foreach($response_decoded_prd->products as $decoded)
            {
               $produtos = ProdutosPHC::where('reference',$decoded->reference)->first();

               if(empty($produtos))
               {
                    ProdutosPHC::create([
                        "reference" => $decoded->reference,
                        "description" => $decoded->description,
                        "service" => $decoded->service,
                        "price" => $decoded->price,
                        "barcode" => $decoded->barcode
                    ]);
               }
            }


            /****** */






                              
        });
       
        
        
    }
}
