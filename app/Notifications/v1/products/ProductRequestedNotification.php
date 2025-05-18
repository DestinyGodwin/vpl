<?php

namespace App\Notifications\v1\products;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use App\Models\ProductRequest;

class ProductRequestedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public ProductRequest $productRequest)
    {
    }

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('New Product Request')
            ->greeting("Hello {$notifiable->first_name},")
            ->line("A user has requested a product that matches your store type.")
            ->line("Product: {$this->productRequest->name}")
            ->line("Description: {$this->productRequest->description}")
            ->action('View Requests', url('/product-requests'))
            ->line('Thanks for using our platform!');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'title' => 'New Product Request',
            'message' => "A user has requested a product that matches your store type: {$this->productRequest->name}",
            'product_request_id' => $this->productRequest->id,
        ];
    }
}
