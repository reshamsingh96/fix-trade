<?php

namespace App\Http\Controllers;

use App\Constants\StatusConst;
use App\Models\BillingAddress;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderStatusHistory;
use App\Models\Product;
use App\Models\ShippingAddress;
use App\Models\Tax;
use App\Models\User;
use App\Models\UserAddress;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    const CONTROLLER_NAME = "App\Http\Controllers\OrderController";
    protected $user;
    protected $isSuperAdmin;
    protected $userUuid;

    private function checkAdminUser()
    {
        $this->user = auth()->user() ?? null;
        $this->isSuperAdmin = $this->user && $this->user->account_type == User::SUPER_ADMIN;
        $this->userUuid = $this->user ? $this->user->uuid : null;
    }

    public function orderList(Request $request)
    {
        try {
            $params = [
                'status' => $request->status ?? 'All',
                'order_type' => $request->order_type ?? 'product', 
                'url_product_id' => $request->url_product_id ?? null, 
                'product_id' => $request->product_id ?? null, 
                'category_id' => $request->category_id ?? null, 
                'sub_category_id' => $request->sub_category_id ?? null, 
                'search' => $request->search ?? null, 
                'row' => $request->perPage];
                
            $list = $this->_orderList(...$params);
            return $this->actionSuccess('Orders retrieved successfully.', $list);
        } catch (\Exception $e) {
            createExceptionError($e, self::CONTROLLER_NAME, __FUNCTION__);
            return $this->actionFailure($e->getMessage());
        }
    }
    private function _orderList(string $status, string $order_type, string $url_product_id = null, string $product_id = null, string $category_id = null, string $sub_category_id = null, string $search = null, int $row = 25)
    {
        $this->checkAdminUser();

        $query = Order::query();

        if ($order_type == 'product') {
            $query->when(!$this->isSuperAdmin, function ($que1) {
                $que1->whereHas('order_item', function ($que) {
                    $que->whereHas('product', function ($qu) {
                        $qu->where('user_id', $this->userUuid);
                    });
                });
            });
        } else {
            $query->when(!$this->isSuperAdmin, function ($que) {
                $que->where('user_id', $this->userUuid);
            });
        }

        if ($status != 'All') {
            $query->where('status', $status);
        }

        $query->whereHas('order_item', function ($que) use ($url_product_id, $product_id, $category_id, $sub_category_id) {
            $que->whereHas('product', function ($qu) use ($url_product_id, $product_id, $category_id, $sub_category_id) {
                if ($url_product_id) {
                    $qu->where('id', $url_product_id);
                }
                if ($product_id) {
                    $qu->where('id', $product_id);
                }

                if ($category_id) {
                    $qu->where('category_id', $category_id);
                }
                if ($sub_category_id) {
                    $qu->where('sub_category_id', $sub_category_id);
                }
            });
        });

        if (!empty($search)) {
            $query->where(function ($que) use ($search) {
                $que->where('slug', 'like', "%$search%")
                    ->orWhere('customer_notes', 'like', "%$search%")
                    ->orWhereHas('user', function ($qu) use ($search) {
                        $qu->where('name', 'like', "%$search%");
                    })
                    ->orWhereHas('guest_user', function ($qu) use ($search) {
                        $qu->where('name', 'like', "%$search%");
                    });
            });
        }
        return $query->latest()->with('user', 'guest_user', 'order_item', 'order_item.store', 'order_item.product')->paginate($row);
    }

    public function orderCreate(Request $request)
    {
        try {
            DB::beginTransaction();
            $product = Product::where('id', $request->product_id)->with('tax')->first();
            if ($product && $product->quantity <= 0) {
                return $this->actionFailure('Product Quantity not available!');
            }

            # Order create
            $order = new Order();
            $order->slug = generateUniqueSlug();
            $order->user_id = auth()->check() ? auth()->user()->uuid : $request->user_id;
            $order->guest_user_id = $request->guest_user_id;
            $order->status = $request->status;
            $order->payment_type = $request->payment_type;
            $order->payment_status = $request->payment_status;
            $order->coupon_id = $request->coupon_id;
            $order->coupon_code = $request->coupon_code;
            $order->coupon_discount_amount = $request->coupon_discount_amount;
            $order->customer_notes = $request->customer_notes;
            $order->gst_amount = $request->gst_amount;
            $order->total_amount = $request->net_payable_amount;
            $order->save();
            if (!$order->id) {
                return $this->actionFailure('Failed to retrieve Order ID');
            }else{
                $order_id = $order->id;
            }


            # Billing Address create
            $user = User::where('uuid', $order->user_id)->first();
            if ($user) {
                $address = UserAddress::where('user_id', $order->user_id)->first();
                $billing_address = new BillingAddress();
                $billing_address->order_id = $order_id;
                $billing_address->name = $request->billing_name ? $request->billing_name : $user->name;
                $billing_address->address_line1 = $request->billing_address_line1 ? $request->billing_address_line1 : $address->full_address;
                $billing_address->address_line2 = $request->billing_address_line2 ? $request->billing_address_line2 : null;
                $billing_address->address_line3 = $request->billing_address_line3 ? $request->billing_address_line3 : null;
                $billing_address->country_id = $request->billing_country_id ? $request->billing_country_id : $address->country_id;
                $billing_address->state_id = $request->billing_state_id ? $request->billing_state_id : $address->state_id;
                $billing_address->city_id = $request->billing_city_id ? $request->billing_city_id : $address->city_id;
                $billing_address->pin_code = $request->billing_pin_code ? $request->billing_pin_code : $address->pin_code;
                $billing_address->phone = $request->billing_phone ? $request->billing_phone : $user->phone;
                $billing_address->email = $request->billing_email ? $request->billing_email : $user->email;
                $billing_address->save();

                $order->billing_address_id = $billing_address->id;
                $order->save();
            }

            # Shipping Address create
            $user = null;
            $user = User::where('uuid', $product->user_id)->first();
            if ($user) {
                $address = null;
                $address = UserAddress::where('user_id', $product->user_id)->first();
                $shipping_address = new ShippingAddress();
                $shipping_address->order_id = $order_id;
                $shipping_address->name = $request->shipping_name ? $request->shipping_name : $user->name;
                $shipping_address->address_line1 = $request->shipping_address_line1 ? $request->shipping_address_line1 : ($address && $address->full_address ? $address->full_address : 'N/A');
                $shipping_address->address_line2 = $request->shipping_address_line2 ? $request->shipping_address_line2 : null;
                $shipping_address->address_line3 = $request->shipping_address_line3 ? $request->shipping_address_line3 : null;
                $shipping_address->country_id = $request->shipping_country_id ? $request->shipping_country_id : ($address ? $address->country_id : null);
                $shipping_address->state_id = $request->shipping_state_id ? $request->shipping_state_id : ($address ? $address->state_id : null);
                $shipping_address->city_id = $request->shipping_city_id ? $request->shipping_city_id : ($address ? $address->city_id : null);
                $shipping_address->pin_code = $request->shipping_pin_code ? $request->shipping_pin_code : ($address ? $address->pin_code : null);
                $shipping_address->phone = $request->shipping_phone ? $request->shipping_phone : $user->phone;
                $shipping_address->email = $request->shipping_email ? $request->shipping_email : $user->email;
                $shipping_address->save();

                $order->shipping_address_id = $shipping_address->id;
                $order->save();
            }

            # Order Status History create
            $history = new OrderStatusHistory();
            $history->order_id = $order_id;
            $history->user_id = User::where('account_type', User::SUPER_ADMIN)->pluck('uuid')->first();
            $history->status =  StatusConst::ORDER_STATUS_PENDING;
            $history->comment = 'New Order Created';
            $history->save();

            # Order item create
            $product = Product::where('id', $request->product_id)->with('tax')->first();
            $amount = $request->sale_price * $request->quantity;
            $order_item = new OrderItem();
            $order_item->order_id = $order_id;
            $order_item->product_id = $product->id;
            $order_item->store_id = $product->store_id;
            $order_item->product_name = $product->name;
            $order_item->order_type = $product->type;
            $order_item->quantity = $request->quantity;
            $order_item->unit_price = $product->unit_price;
            $order_item->discount_amount = $product->discount;
            $order_item->duration = $request->duration;
            $order_item->tax_type = $product->tax_type; // ['Inclusive', 'Exclusive']
            $order_item->tax_id = $product->tax_id ?? Tax::first()->id ;
            $order_item->gst_rate = $product && $product->tax ? $product->tax->tax_percentage : 0;
            $order_item->sale_price = $request->sale_price;
            $order_item->gst_amount = gstAmount($amount, $order_item->gst_rate, $product->tax_type);
            $order_item->total_price = round($amount + $order_item->gst_amount);
            $order_item->save();
            if ($product && $product->type != StatusConst::ORDER_TYPE_RENT) {
                $quantity = $product->quantity - $request->quantity;
                $product->quantity = $quantity > 0 ? $quantity : 0;
                $product->save();
            }
            DB::commit();
            return $this->actionSuccess('Order created successfully.', $order);
        } catch (\Exception $e) {
            DB::rollback();
            createExceptionError($e, self::CONTROLLER_NAME, __FUNCTION__);
            return $this->actionFailure('Failed to create order: ' . $e->getMessage());
        }
    }

    public function singleOrderInfo(Request $request)
    {
        try {
            $order = Order::where('id', $request->order_id)->with('user', 'guest_user', 'coupon', 'order_item', 'order_item.store', 'order_item.product')->first();
            return $this->actionSuccess('Order info retrieved successfully.', $order);
        } catch (\Exception $e) {
            createExceptionError($e, self::CONTROLLER_NAME, __FUNCTION__);
            return $this->actionFailure('Failed to retrieve order info: ' . $e->getMessage());
        }
    }

    public function orderUpdate(Request $request)
    {
        try {
            DB::beginTransaction();
            $order = null;
            // Validate and update order logic here
            // Example: $order = Order::find($request->id);
            // $order->update($request->all());

            DB::commit();
            return $this->actionSuccess('Order updated successfully.', $order);
        } catch (\Exception $e) {
            DB::rollback();
            createExceptionError($e, self::CONTROLLER_NAME, __FUNCTION__);
            return $this->actionFailure('Failed to update order: ' . $e->getMessage());
        }
    }

    public function orderStatusUpdate(Request $request){
        $request->validate([
            'order_id' => 'required|uuid|exists:orders,id',
            'status' => 'required|string|in:Pending,Progress,Delivered,Completed,Cancel',
            'payment_status' => 'required|string|in:Pending,Paid,Failed'
        ]);

        try {
            DB::beginTransaction();
            $order = Order::where('id',$request->order_id)->first();
            $order->status = $request->status;
            $order->payment_status = $request->payment_status;
            $order->save();
            DB::commit();
            return $this->actionSuccess('Order status updated successfully',$order);
        } catch (\Exception $e) {
            DB::rollback();
            createExceptionError($e, self::CONTROLLER_NAME, __FUNCTION__);
            return $this->actionFailure( $e->getMessage());
        }
    }

    public function orderDelete(Request $request)
    {
        try {
            DB::beginTransaction();

            $order = Order::find($request->id);
            $order->delete();

            DB::commit();
            return $this->actionSuccess('Order deleted successfully.');
        } catch (\Exception $e) {
            DB::rollback();
            createExceptionError($e, self::CONTROLLER_NAME, __FUNCTION__);
            return $this->actionFailure('Failed to delete order: ' . $e->getMessage());
        }
    }
}
