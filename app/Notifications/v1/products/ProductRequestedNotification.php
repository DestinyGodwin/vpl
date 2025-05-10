<?php

namespace App\Notifications\v1\products;

use Illuminate\Bus\Queueable;
use App\Models\ProductRequest;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

class ProductRequestedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(public ProductRequest $productRequest)
    {
        //
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail($notifiable)
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

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            //
        ];
    }
}
