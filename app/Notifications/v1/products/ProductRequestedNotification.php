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

    public ProductRequest $productRequest;

    /**
     * Create a new notification instance.
     */
    public function __construct(ProductRequest $productRequest)
    {
        $this->productRequest = $productRequest->fresh(); 
    }

    /**
     * Get the notification's delivery channels.
     */
    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('New Product Request')
            ->greeting("Hello {$notifiable->first_name},")
            ->line("A user has requested a product on Vplaza.")
            ->line("Log in to your account and navigate to 'Alert' to view the notification.")
            ->line("Product: {$this->productRequest->name}")
            ->line("Description: {$this->productRequest->description}")
            ->action(                'View Request',url('/product-requests/' . $this->productRequest->getKey()))
            ->line('Thanks for using our platform!');
    }

    /**
     * Get the array representation of the notification for database storage.
     */
    public function toArray(object $notifiable): array
    {
        return [
            'title' => 'New Product Request',
            'message' => "A user has requested a product that you may have: {$this->productRequest->name}",
            'product_request_id' => $this->productRequest->id,
        ];
    }
}
