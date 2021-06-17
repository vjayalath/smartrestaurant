<?php

namespace App\Http\Livewire\Kitchen;

use App\Models\Order;
use App\Models\OrderDetails;
use Livewire\Component;

class Orders extends Component
{
    public $new_orders = [], $received_orders = [], $processed_orders = [], $play_sound = false;

    protected $listeners = ['orderAdded' => '$refresh'];

    private function setNewOrders(){
        $orders = Order::newOrders()->get();

        $this->new_orders = [];

        foreach($orders as $order){
            $order_details = OrderDetails::belongsToOrder($order->id)->isFood()->get();
            if ($order_details && sizeof($order_details) > 0) {
                $this->new_orders[] = $order_details;
            }
        }

    }

    private function setReceivedOrders(){
        $orders = Order::receivedOrders()->with('user.warehouse')->get();

        $this->received_orders = [];

        foreach($orders as $order){
            $order_details = OrderDetails::belongsToOrder($order->id)->isFood()->get();
            if ($order_details && sizeof($order_details) > 0) {
                $new_order = [
                    'order' => $order,
                    'order_details' => $order_details
                ];

                $this->received_orders[] = $new_order;
            }
        }
    }

    private function setProcessedOrders(){
        $orders = Order::processedOrders()->with('user.warehouse')->latest()->limit(15)->get();

        $this->processed_orders = [];

        foreach($orders as $order){
            $order_details = OrderDetails::belongsToOrder($order->id)->isFood()->get();
            if ($order_details && sizeof($order_details) > 0) {
                $new_order = [
                    'order' => $order,
                    'order_details' => $order_details
                ];
                $this->processed_orders[] = $new_order;
            }
        }
    }

    public function receiveOrder($order){
        $order = Order::find($order[0]['order_id']);
        $order->status = 'received';
        $order->save();

        $this->setNewOrders();
        $this->setReceivedOrders();
    }

    public function processOrder($order){
        $order = Order::find($order['id']);
        $order->status = 'processed';
        $order->save();

        $this->setProcessedOrders();
        $this->setReceivedOrders();

    }



    public function cancelOrder($order){
        Order::find($order['id'])->delete();
        OrderDetails::whereOrderId($order['id'])->delete();

        $this->setReceivedOrders();
    }


    public function render()
    {
        $this->setNewOrders();
        $this->setReceivedOrders();
        $this->setProcessedOrders();

        return view('livewire.kitchen.orders');
    }
}
