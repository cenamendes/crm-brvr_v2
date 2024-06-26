<div>
<div id="ajaxLoading" wire:loading.flex class="w-100 h-100 flex "
style="background:rgba(255, 255, 255, 0.8);z-index:999;position:fixed;top:0;left:0;align-items: center;justify-content: center;">
  <div class="sk-three-bounce" style="background:none;">
      <div class="sk-child sk-bounce1"></div>
      <div class="sk-child sk-bounce2"></div>
      <div class="sk-child sk-bounce3"></div>
  </div>
</div>
<div class="modal fade" id="modalInfo" data-id="" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLabel">Informação Pedido</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
       
      </div>
      <div class="modal-footer">
        <button type='button' id='abrirIntervencaoButton' class='btn btn-success' style='font-size: 12px;'>Abrir Intervenção</button>
        <button type='button' id='consultarPedidoButton' class='btn btn-danger' style='font-size: 12px;'>Consultar Pedido</button>
      </div>
    </div>
  </div>
</div>
<div class="container-fluid">
  <div class="row">
    <div class="col-xl-12">
      <div class="row">
       
        <div class="col-xl-12" style="height:50%;">
          {{-- <div class="row"> --}}
            <div class="card">
              <div class="card-header">
                <h4 class="card-title">Pedidos Abertos</h4>
              </div>
              <div class="card-body" style="display:flex;overflow:auto;">

            <div class="table-responsive" style="position: relative;">
              {{-- class="display dataTable no-footer" --}}
              <table id="dataTables-data" class="table table-responsive-lg mb-0 table-striped">
                  <thead>
                      <tr>
                        <th>{{ __('Reference') }}</th>
                        <th>{{ __('Customer') }}</th>
                        <th>Serviço</th>
                        <th>{{ __('Technical') }}</th>
                        <th>{{ __('Date') }}</th>
                        <th>{{ __('County') }}</th>
                        <th>{{ __('Estado do Pedido') }}</th>
                      </tr>
                  </thead>
                  <tbody>
                    @foreach ($pedidos as $item)
                       @php
                         $cust = $customersRepository->getSpecificCustomerInfo($item->customer_id);
                       @endphp
                      <tr id="pedidoLinha" data-id="{{$item->id}}" data-cliente="{{str_replace(' ', '£', $cust->customers->name)}}" data-referencia="{{$item->reference}}" style="background:{{ $item->prioridadeStat->cor }};">
              
                        <td>{{ $item->reference }}</td>
                        <td>{{ $cust->customers->name }}</td>
                        <td>{{ $item->descricao }}</td>
                        <td>{{ $item->tech->name }}</td>
                        <td>
                            @if($item->data_agendamento != "")
            
                            <i class="fa fa-calendar" aria-hidden="true"></i> {{ $item->data_agendamento }}<br>
                            <i class="fa fa-clock-o" aria-hidden="true"></i> {{ $item->hora_agendamento }}
                            @else
                            <i class="fa fa-calendar" aria-hidden="true"></i> {{ date('Y-m-d',strtotime($item->created_at)) }}<br>
                            <i class="fa fa-clock-o" aria-hidden="true"></i> {{ date('H:i',strtotime($item->created_at)) }}
                            @endif
                        </td>
                        <td>
                          @php
                              $locations = $customerLocationInterface->getSpecificLocationInfo($item->location_id); 
                          @endphp
                          {{ $locations->locations->address }}
                        </td>

                        <td>{{ $item->tipoEstado->nome_estado }}</td>
                      </tr> 
                    @endforeach
                  </tbody>
              </table>
          </div>
        {{-- </div> --}}

          </div>
          </div>

          <div class="card">
            <div class="card-header">
              <h4 class="card-title">Pedidos Concluídos</h4>
            </div>
            <div class="card-body" style="display:flex;overflow:auto;">

          <div class="table-responsive" style="position: relative;">
            {{-- class="display dataTable no-footer" --}}
            <table id="dataTables-data" class="table table-responsive-lg mb-0 table-striped">
                <thead>
                    <tr>
                      <th>{{ __('Reference') }}</th>
                      <th>{{ __('Customer') }}</th>
                      <th>Serviço</th>
                      <th>{{ __('Technical') }}</th>
                      <th>{{ __('Date') }}</th>
                      <th>{{ __('County') }}</th>
                      <th>{{ __('Estado do Pedido') }}</th>
                    </tr>
                </thead>
                <tbody>
                  @foreach ($pedidosconcluidos as $item)
                     @php
                       $cust = $customersRepository->getSpecificCustomerInfo($item->customer_id);
                     @endphp
                    <tr id="pedidoLinha" data-id="{{$item->id}}" data-cliente="{{str_replace(' ', '£', $cust->customers->name)}}" data-referencia="{{$item->reference}}" style="background:{{ $item->prioridadeStat->cor }};">
            
                      <td>{{ $item->reference }}</td>
                      <td>{{ $cust->customers->name }}</td>
                      <td>{{ $item->descricao }}</td>
                      <td>{{ $item->tech->name }}</td>
                      <td>
                          @if($item->data_agendamento != "")
          
                          <i class="fa fa-calendar" aria-hidden="true"></i> {{ $item->data_agendamento }}<br>
                          <i class="fa fa-clock-o" aria-hidden="true"></i> {{ $item->hora_agendamento }}
                          @else
                          <i class="fa fa-calendar" aria-hidden="true"></i> {{ date('Y-m-d',strtotime($item->created_at)) }}<br>
                          <i class="fa fa-clock-o" aria-hidden="true"></i> {{ date('H:i',strtotime($item->created_at)) }}
                          @endif
                      </td>
                      <td>
                        @php
                            $locations = $customerLocationInterface->getSpecificLocationInfo($item->location_id); 
                        @endphp
                        {{ $locations->locations->address }}
                      </td>

                      <td>{{ $item->tipoEstado->nome_estado }}</td>
                    </tr> 
                  @endforeach
                </tbody>
            </table>
        </div>
      {{-- </div> --}}

        </div>
        </div>

          {{-- @if(Auth::user()->type_user == 0) --}}

            <div class="col-xl-12" style="margin-top:20px;padding-left:0px;padding-right:0px;">
              <div class="card">
                <div class="card-header">
                  <h4 class="card-title">Intervenções em aberto</h4>
                </div>
                <div class="card-body" style="display:flex;overflow:auto;">
                  <table class="table mb-4 dataTablesCard no-hover card-table fs-14 dataTable no-footer" id="data5" role="grid" aria-describedby="data5_info">
                      <thead>
                        <tr role="row" style="background:#326c91;">
                          <th class="sorting" style="color:white;" tabindex="0" aria-controls="data5" rowspan="1" colspan="1">{{ __('Technical') }}</th>
                          <th class="sorting" style="color:white;" tabindex="0" aria-controls="data5" rowspan="1" colspan="1">{{ __('Customer') }}</th>
                          <th class="d-lg-inline-block sorting" style="color:white;" tabindex="0" aria-controls="data5" rowspan="1" colspan="1">Pedido</th>
                          <th class="sorting" style="color:white;" tabindex="0" aria-controls="data5" rowspan="1" colspan="1">{{ __('Time used') }}</th>
                          <th></th>
                        </tr> 
                      </thead>
                      <tbody>
                        @foreach($openTimes as $name => $time)
                          @if(!empty($time))
                            @foreach ($time as $des)
                              <tr>
                                <td>
                                <h4>
                                    <a href="javascript:void(0)" class="text-black">{{ $name }}</a>
                                </h4>
                                </td>
                                <td>{{ $des["cliente"] }}</td>
                                <td>
                                  <i class="fa fa-tasks" aria-hidden="true"></i>
                                  {{ $des["reference"] }} <br>
                                </td>
                                <td>
                                  <i class="fa fa-calendar" aria-hidden="true"></i>
                                  {{ date('Y-m-d',strtotime($des["data"])) }} <br>
                                  <i class="fa fa-clock-o" aria-hidden="true"></i>
                                  {{ date('H:i:s',strtotime($des["data"])) }}
                                </td>
                                <td>
                                  <div class="d-flex">
                                    @if (Auth::user()->type_user == 0 || (Auth::user()->id == $des["tecnico"]))
                                      <a href="{{ route('tenant.tasks-reports.edit', $des["idpedido"])}}" class="btn btn-primary shadow  sharp mr-1">
                                        <i class="fa fa-pencil"></i>
                                      </a>
                                    @endif
                                  </div>
                                </td>
                              </tr>
                            @endforeach
                          @endif
                        @endforeach
                      </tbody>
                  </table>
                </div>
              </div>
            </div> 
            
          {{-- @endif --}}

        <div class="col-xl-12" style="margin-top:20px;padding-right:0;padding-left:0;">
          <div class="card">
            <div class="card-header">
              <h4 class="card-title">{{ __("Notifications")}}</h4>
            </div>
            <div class="card-body" style="display:flex;overflow:auto;">
              <table id="dataTables-data" class="table table-responsive-lg mb-0 table-striped">
                <thead>
                  <tr>
                    <th>{{ __('Service') }}</th>
                    <th>{{ __('Technical') }}</th>
                    <th>{{ __('Customer') }}</th>
                    <th>{{ __('Customer Location') }}</th>                   
                    <th>{{ __('Notification day') }}</th>
                  </tr>
                </thead>
                <tbody>
                  @if ($servicesNotifications != null)
                    @foreach ($servicesNotifications as $notification)
                      @if ($notification["team_member"] ==Auth::user()->name ||Auth::user()->type_user == 0)
                          <tr>
                            <td>{{$notification["service"]}}</td>
                            <td>{{$notification["team_member"]}}</td>
                            <td>{{$notification["customer"]}}</td>
                            <td>{{$notification["customer_county"]}}</td>
                            <td>{{$notification["notification"]}}</td>
                            <td>
                              <div class="d-flex">
                                <button href="javascript:void(0)" wire:click="treated({{$notification["customerServicesId"]}})" class="btn btn-primary btn-sm light px-4">{{__("Treated")}}</button>
                              </div>
                            </td>
                          </tr>
                        @endif
                    @endforeach
                  @endif
                </tbody>
              </table>
            </div>
          </div>
        </div>
      
    </div>
    </div>
  </div>
</div>

@push('custom-scripts')
<script>
  jQuery( document ).ready(function() {


    window.addEventListener('interventionCheck',function(e){

        var nome_text = e.detail.parameter;
        var referencia = e.detail.reference;
        var idPedido = e.detail.idPedido;
        var cliente = e.detail.cliente;
        var phone = e.detail.phone;
        var quem_pediu = e.detail.quem_pediu;

        var textButtonConfirm = "";
        var showConfirmButtonResponse;

         if(nome_text == "fechar"){
          textButtonConfirm = "Fechar Intervenção";
          showConfirmButtonResponse = true;
        } else if(nome_text == "abrir") {
          textButtonConfirm = "Abrir Intervenção";
          showConfirmButtonResponse = true;
        } else {
          textButtonConfirm = "";
          showConfirmButtonResponse = false;
        }

        var message = "Referência: "+referencia+ "<br>Cliente: "+cliente+"<br>Quem pediu: "+quem_pediu;

        if(phone != "")
        {
          message += "<br>Telemóvel: "+phone;
        }

        swal.fire({
                title: "Informação Pedido",
                html: message,
                confirmButtonText:textButtonConfirm,
                showCancelButton: true,
                showConfirmButton: showConfirmButtonResponse,
                cancelButtonText: "Consultar Pedido",
                type: "info",
                onOpen: function() {

                }

            }).then((result) => {  

            
              if(result.value == true) {
                Livewire.emit("intervencaoCheck",e.detail.idPedido);
              } else if(result.dismiss == "cancel") {

                window.location.href="tasks/"+e.detail.idPedido+"/edit";
              }
                           
                              
            });


       

        // jQuery(".modal-body").empty();

        // jQuery('#modalInfo').modal('show');
        // jQuery(".modal-body").append("Referência: "+referencia+ "<br>Cliente: "+cliente); 

        

        // jQuery("body").on("click","#abrirIntervencaoButton",function(){
        
        //   jQuery('#modalInfo').modal('hide');
        //   console.log(idPedido);
        //   Livewire.emit("intervencaoCheck",e.detail.idPedido);

        // });

        // jQuery("body").on("click","#consultarPedidoButton",function(){

        //     window.location.href="tasks/"+e.detail.idPedido+"/edit";
        // });

        
    });

    window.addEventListener('refreshserviceTable', function(e) {
     
      Livewire.emit("refreshserviceTable");
    });


  });
</script>
@endpush

</div>