<?php

namespace App\Events\Tasks;

use App\Models\Tenant\Tasks;
use App\Models\Tenant\Pedidos;
use App\Models\Tenant\TasksReports;
use Illuminate\Broadcasting\Channel;
use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class SendPDF
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $valuesInfo;
    public $pdf;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(Pedidos $values, $pdf)
    {
        $this->valuesInfo = $values;
        $this->pdf = $pdf;
    }

}
