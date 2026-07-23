<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Product;
use App\Repositories\Contracts\CartRepositoryInterface;
use App\Repositories\Contracts\OrderRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class OrderService
{
    public function __construct(
        protected OrderRepositoryInterface $orderRepository,
        protected CartRepositoryInterface $cartRepository
    ) {}

    public function getUserOrders(int $userId): Collection
    {
        return $this->orderRepository->findByUser($userId);
    }

    public function getById(int $id, int $userId): Order
    {
        $order = $this->orderRepository->find($id);

        if (! $order || $order->user_id !== $userId) {
            throw ValidationException::withMessages([
                'order' => 'Order tidak ditemukan.',
            ]);
        }

        return $order;
    }

    public function checkout(int $userId, array $shippingData, string $paymentMethod, array $itemsPayload = []): Order
    {
        $cart = $this->cartRepository->getOrCreateForUser($userId);
        $cartItems = $cart->items;

        if ($cartItems->isEmpty() && empty($itemsPayload)) {
            throw ValidationException::withMessages([
                'cart' => 'Keranjang Anda masih kosong.',
            ]);
        }

        $orderItems = [];

        if ($cartItems->isNotEmpty()) {
            foreach ($cartItems as $item) {
                if ($item->qty > $item->product->stock) {
                    throw ValidationException::withMessages([
                        'stock' => "Stok {$item->product->name} tidak mencukupi.",
                    ]);
                }

                $orderItems[] = [
                    'product_id' => $item->product_id,
                    'product_name' => $item->product->name,
                    'price' => $item->product->price,
                    'unit' => $item->product->unit,
                    'qty' => $item->qty,
                    'subtotal' => $item->product->price * $item->qty,
                    'product' => $item->product,
                ];
            }
        } else {
            foreach ($itemsPayload as $payload) {
                $product = Product::find($payload['product_id']);

                if (! $product || ! $product->is_active || $product->status !== 'approved') {
                    throw ValidationException::withMessages([
                        'product' => 'Produk tidak tersedia.',
                    ]);
                }

                if ($payload['qty'] > $product->stock) {
                    throw ValidationException::withMessages([
                        'stock' => "Stok {$product->name} tidak mencukupi.",
                    ]);
                }

                $orderItems[] = [
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'price' => $product->price,
                    'unit' => $product->unit,
                    'qty' => $payload['qty'],
                    'subtotal' => $product->price * $payload['qty'],
                    'product' => $product,
                ];
            }
        }

        return DB::transaction(function () use ($cart, $userId, $shippingData, $paymentMethod, $orderItems) {
            $subtotal = collect($orderItems)->sum(fn ($item) => $item['subtotal']);
            $shippingCost = 0; // FREE shipping, sesuai frontend
            $tax = round($subtotal * 0.08, 2); // 8%, sesuai frontend (taxRate = 0.08)
            $total = $subtotal + $shippingCost + $tax;

            $order = $this->orderRepository->create([
                'order_number' => 'FH-' . strtoupper(Str::random(8)),
                'user_id' => $userId,
                'status' => 'pending',
                'recipient_name' => $shippingData['recipient_name'],
                'phone' => $shippingData['phone'],
                'street' => $shippingData['street'],
                'suite' => $shippingData['suite'] ?? null,
                'city_state_zip' => $shippingData['city_state_zip'],
                'subtotal' => $subtotal,
                'shipping_cost' => $shippingCost,
                'tax' => $tax,
                'total' => $total,
            ]);

            foreach ($orderItems as $orderItem) {
                $order->items()->create([
                    'product_id' => $orderItem['product_id'],
                    'product_name' => $orderItem['product_name'],
                    'price' => $orderItem['price'],
                    'unit' => $orderItem['unit'],
                    'qty' => $orderItem['qty'],
                    'subtotal' => $orderItem['subtotal'],
                ]);

                // Kurangi stok produk
                $orderItem['product']->decrement('stock', $orderItem['qty']);
            }

            $order->payment()->create([
                'method' => $paymentMethod,
                'status' => $paymentMethod === 'cod' ? 'pending' : 'pending',
            ]);

            // Kosongkan cart setelah checkout berhasil
            $this->cartRepository->clear($cart->id);

            return $order->load(['items.product.images', 'payment']);
        });
    }

    public function confirmPayment(int $orderId, int $userId): Order
    {
        $order = $this->getById($orderId, $userId);

        $order->payment()->update([
            'status' => 'paid',
            'paid_at' => now(),
        ]);

        $order->update(['status' => 'diproses']);

        return $order->load(['items.product.images', 'payment']);
    }
}