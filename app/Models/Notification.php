<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    use HasFactory;

    // Use a distinct table name to avoid colliding with Laravel's notifications table
    protected $table = 'user_notifications';

    protected $fillable = [
        'user_id',
        'title',
        'message',
        'type',
        'data',
        'is_read',
        'read_at',
    ];

    protected $casts = [
        'data' => 'array',
        'is_read' => 'boolean',
        'read_at' => 'datetime',
    ];

    /**
     * Notification types
     */
    public const TYPE_TASK_APPROVED = 'task_approved';
    public const TYPE_TASK_REJECTED = 'task_rejected';
    public const TYPE_NEW_TASK = 'new_task';
    public const TYPE_EARNINGS = 'earnings';
    public const TYPE_WITHDRAWAL = 'withdrawal';
    public const TYPE_LEVEL_UP = 'level_up';
    public const TYPE_BADGE_EARNED = 'badge_earned';
    public const TYPE_STREAK_REWARD = 'streak_reward';
     public const TYPE_TASK_EXPIRY = 'task_expiry';
     public const TYPE_REFERRAL = 'referral';
     public const TYPE_SYSTEM = 'system';
     public const TYPE_PAYMENT = 'payment';
     public const TYPE_FEATURE_EXPIRING_SOON = 'feature_expiring_soon';
     public const TYPE_FEATURE_EXPIRED = 'feature_expired';
     public const TYPE_JOB_CREATED = 'job_created';
     public const TYPE_JOB_APPLICATION_SUBMITTED = 'job_application_submitted';
     public const TYPE_JOB_APPLICANT_HIRED = 'job_applicant_hired';
     public const TYPE_JOB_APPLICANT_REJECTED = 'job_applicant_rejected';
     public const TYPE_JOB_CLOSED = 'job_closed';
     public const TYPE_DIGITAL_PRODUCT_CREATED = 'digital_product_created';
     public const TYPE_DIGITAL_PRODUCT_APPROVED = 'digital_product_approved';
     public const TYPE_DIGITAL_PRODUCT_REJECTED = 'digital_product_rejected';
     public const TYPE_DIGITAL_PRODUCT_PURCHASED = 'digital_product_purchased';
     public const TYPE_DIGITAL_PRODUCT_DOWNLOADED = 'digital_product_downloaded';
     public const TYPE_DIGITAL_PRODUCT_CONFIRMED_SELLER = 'digital_product_confirmed_seller';
     public const TYPE_DIGITAL_PRODUCT_CONFIRMED_BUYER = 'digital_product_confirmed_buyer';

    /**
     * Relationship: User
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope: Unread notifications
     */
    public function scopeUnread($query)
    {
        return $query->where('is_read', false);
    }

    /**
     * Scope: By type
     */
    public function scopeByType($query, string $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Scope: Recent
     */
    public function scopeRecent($query, int $days = 7)
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }

    /**
     * Mark as read
     */
    public function markAsRead(): bool
    {
        if ($this->is_read) {
            return false;
        }

        $this->is_read = true;
        $this->read_at = now();
        $this->save();

        return true;
    }

    /**
     * Get formatted created date
     */
    public function getFormattedDateAttribute(): string
    {
        return $this->created_at->diffForHumans();
    }

    /**
     * Get icon based on type
     */
    public function getIconAttribute(): string
    {
        $icons = [
            self::TYPE_TASK_APPROVED => 'fas fa-check-circle',
            self::TYPE_TASK_REJECTED => 'fas fa-times-circle',
            self::TYPE_NEW_TASK => 'fas fa-tasks',
            self::TYPE_EARNINGS => 'fas fa-naira-sign',
            self::TYPE_WITHDRAWAL => 'fas fa-bank',
            self::TYPE_LEVEL_UP => 'fas fa-level-up-alt',
            self::TYPE_BADGE_EARNED => 'fas fa-medal',
            self::TYPE_STREAK_REWARD => 'fas fa-fire',
             self::TYPE_TASK_EXPIRY => 'fas fa-exclamation-triangle',
             self::TYPE_REFERRAL => 'fas fa-users',
             self::TYPE_SYSTEM => 'fas fa-bell',
             self::TYPE_PAYMENT => 'fas fa-credit-card',
             self::TYPE_FEATURE_EXPIRING_SOON => 'fas fa-clock',
             self::TYPE_FEATURE_EXPIRED => 'fas fa-times-circle',
        ];

         return $icons[$this->type] ?? 'fas fa-bell';
     }

     /**
      * Get color based on type
      */
     public function getColorAttribute(): string
     {
         $colors = [
             self::TYPE_TASK_APPROVED => 'success',
             self::TYPE_TASK_REJECTED => 'danger',
             self::TYPE_NEW_TASK => 'primary',
             self::TYPE_EARNINGS => 'success',
             self::TYPE_WITHDRAWAL => 'info',
             self::TYPE_LEVEL_UP => 'warning',
             self::TYPE_BADGE_EARNED => 'warning',
             self::TYPE_STREAK_REWARD => 'warning',
              self::TYPE_TASK_EXPIRY => 'danger',
              self::TYPE_REFERRAL => 'info',
              self::TYPE_SYSTEM => 'secondary',
              self::TYPE_PAYMENT => 'primary',
              self::TYPE_FEATURE_EXPIRING_SOON => 'warning',
              self::TYPE_FEATURE_EXPIRED => 'danger',
              self::TYPE_JOB_CREATED => 'info',
              self::TYPE_JOB_APPLICATION_SUBMITTED => 'primary',
              self::TYPE_JOB_APPLICANT_HIRED => 'success',
              self::TYPE_JOB_APPLICANT_REJECTED => 'danger',
              self::TYPE_JOB_CLOSED => 'warning',
              self::TYPE_DIGITAL_PRODUCT_CREATED => 'info',
              self::TYPE_DIGITAL_PRODUCT_APPROVED => 'success',
              self::TYPE_DIGITAL_PRODUCT_REJECTED => 'danger',
              self::TYPE_DIGITAL_PRODUCT_PURCHASED => 'primary',
              self::TYPE_DIGITAL_PRODUCT_DOWNLOADED => 'info',
              self::TYPE_DIGITAL_PRODUCT_CONFIRMED_SELLER => 'success',
              self::TYPE_DIGITAL_PRODUCT_CONFIRMED_BUYER => 'success',
         ];

         return $colors[$this->type] ?? 'secondary';
     }

    /**
     * Send notification to user
     */
    public static function sendTo(User $user, string $title, string $message, string $type = self::TYPE_SYSTEM, array $data = []): self
    {
        return self::create([
            'user_id' => $user->id,
            'title' => $title,
            'message' => $message,
            'type' => $type,
            'data' => $data,
            'is_read' => false,
        ]);
    }

    /**
     * Notify task approved
     */
    public static function taskApproved(User $user, Task $task, float $amount): self
    {
        return self::sendTo(
            $user,
            'Task Approved! 🎉',
            'Your submission for "' . $task->title . '" has been approved. You earned ₦' . number_format($amount, 2),
            self::TYPE_TASK_APPROVED,
            ['task_id' => $task->id, 'amount' => $amount]
        );
    }

    /**
     * Notify task rejected
     */
    public static function taskRejected(User $user, Task $task, string $reason): self
    {
        return self::sendTo(
            $user,
            'Task Submission Rejected',
            'Your submission for "' . $task->title . '" was rejected: ' . $reason,
            self::TYPE_TASK_REJECTED,
            ['task_id' => $task->id, 'reason' => $reason]
        );
    }

    /**
     * Notify new task available
     */
    public static function newTaskAvailable(User $user, Task $task): self
    {
        return self::sendTo(
            $user,
            'New Task Available!',
            'New task: "' . $task->title . '" - Earn up to ₦' . number_format($task->worker_reward_per_task, 2),
            self::TYPE_NEW_TASK,
            ['task_id' => $task->id]
        );
    }

    /**
     * Notify earnings
     */
    public static function earningsUpdate(User $user, float $amount): self
    {
        return self::sendTo(
            $user,
            'Earnings Received! 💰',
            'You received ₦' . number_format($amount, 2) . ' in your wallet.',
            self::TYPE_EARNINGS,
            ['amount' => $amount]
        );
    }

    /**
     * Notify withdrawal
     */
    public static function withdrawalUpdate(User $user, float $amount, string $status): self
    {
        $amountFormatted = number_format($amount, 2);
        
        if ($status === 'completed') {
            $message = 'Your withdrawal of ₦' . $amountFormatted . ' has been completed.';
        } elseif ($status === 'pending') {
            $message = 'Your withdrawal of ₦' . $amountFormatted . ' is pending approval.';
        } elseif ($status === 'rejected') {
            $message = 'Your withdrawal of ₦' . $amountFormatted . ' was rejected.';
        } else {
            $message = 'Withdrawal status updated.';
        }

        return self::sendTo(
            $user,
            'Withdrawal Update',
            $message,
            self::TYPE_WITHDRAWAL,
            ['amount' => $amount, 'status' => $status]
        );
    }

     /**
      * Notify task expiry reminder
      */
     public static function taskExpiryReminder(User $user, Task $task, int $hoursLeft): self
     {
         return self::sendTo(
             $user,
             'Task Expiring Soon! ⏰',
             'Your task "' . $task->title . '" expires in ' . $hoursLeft . ' hours.',
             self::TYPE_TASK_EXPIRY,
             ['task_id' => $task->id, 'hours_left' => $hoursLeft]
         );
     }

     /**
      * Notify job created
      */
     public static function jobCreated(User $user, Job $job): self
     {
         return self::sendTo(
             $user,
             'Job Posted Successfully! 💼',
             'Your job "' . $job->title . '" has been created and is now live.',
             self::TYPE_JOB_CREATED,
             ['job_id' => $job->id]
         );
     }

     /**
      * Notify job application submitted
      */
     public static function jobApplicationSubmitted(User $user, JobApplication $application): self
     {
         return self::sendTo(
             $user,
             'Application Submitted! 📝',
             'Your application for "' . $application->job->title . '" has been submitted successfully.',
             self::TYPE_JOB_APPLICATION_SUBMITTED,
             ['application_id' => $application->id, 'job_id' => $application->job->id]
         );
     }

     /**
      * Notify job applicant hired
      */
     public static function jobApplicantHired(User $user, JobApplication $application): self
     {
         return self::sendTo(
             $user,
             'Congratulations! You\'ve Been Hired! 🎉',
             'You have been hired for the job "' . $application->job->title . '".',
             self::TYPE_JOB_APPLICANT_HIRED,
             ['application_id' => $application->id, 'job_id' => $application->job->id]
         );
     }

     /**
      * Notify job applicant rejected
      */
     public static function jobApplicantRejected(User $user, JobApplication $application): self
     {
         return self::sendTo(
             $user,
             'Application Update',
             'Your application for "' . $application->job->title . '" has been reviewed and we will be moving forward with other candidates.',
             self::TYPE_JOB_APPLICANT_REJECTED,
             ['application_id' => $application->id, 'job_id' => $application->job->id]
         );
     }

      /**
       * Notify job closed
       */
      public static function jobClosed(User $user, Job $job): self
      {
          return self::sendTo(
              $user,
              'Job Closed',
              'Your job "' . $job->title . '" has been closed.',
              self::TYPE_JOB_CLOSED,
              ['job_id' => $job->id]
          );
      }

      /**
       * Notify digital product created
       */
      public static function digitalProductCreated(User $user, DigitalProduct $product): self
      {
          return self::sendTo(
              $user,
              'Digital Product Created! 📦',
              'Your digital product "' . $product->title . '" has been created successfully.',
              self::TYPE_DIGITAL_PRODUCT_CREATED,
              ['product_id' => $product->id]
          );
      }

      /**
       * Notify digital product approved
       */
      public static function digitalProductApproved(User $user, DigitalProduct $product): self
      {
          return self::sendTo(
              $user,
              'Digital Product Approved! ✅',
              'Your digital product "' . $product->title . '" has been approved and is now live!',
              self::TYPE_DIGITAL_PRODUCT_APPROVED,
              ['product_id' => $product->id]
          );
      }

      /**
       * Notify digital product rejected
       */
      public static function digitalProductRejected(User $user, DigitalProduct $product, string $reason): self
      {
          return self::sendTo(
              $user,
              'Digital Product Rejected',
              'Your digital product "' . $product->title . '" was not approved. Reason: ' . $reason,
              self::TYPE_DIGITAL_PRODUCT_REJECTED,
              ['product_id' => $product->id, 'reason' => $reason]
          );
      }

      /**
       * Notify digital product purchased
       */
      public static function digitalProductPurchased(User $user, DigitalProductOrder $order): self
      {
          return self::sendTo(
              $user,
              'Digital Product Purchased Successfully',
              'You have successfully purchased "' . $order->product->title . '" for ₦' . number_format($order->amount, 2),
              self::TYPE_DIGITAL_PRODUCT_PURCHASED,
              ['product_id' => $order->product->id, 'order_id' => $order->id]
          );
      }

      /**
       * Notify digital product downloaded
       */
      public static function digitalProductDownloaded(User $user, DigitalProductOrder $order): self
      {
          return self::sendTo(
              $user,
              'Digital Product Downloaded',
              'Your download for "' . $order->product->title . '" is ready.',
              self::TYPE_DIGITAL_PRODUCT_DOWNLOADED,
              ['product_id' => $order->product->id, 'order_id' => $order->id]
          );
      }

      /**
       * Notify digital product purchase confirmed (seller)
       */
      public static function digitalProductConfirmedSeller(User $user, DigitalProductOrder $order): self
      {
          return self::sendTo(
              $user,
              'Digital Product Payment Released',
              'Buyer confirmed receipt and reviewed "' . $order->product->title . '". Your payout has been released.',
              self::TYPE_DIGITAL_PRODUCT_CONFIRMED_SELLER,
              ['product_id' => $order->product->id, 'order_id' => $order->id]
          );
      }

      /**
       * Notify digital product purchase confirmed (buyer)
       */
      public static function digitalProductConfirmedBuyer(User $user, DigitalProductOrder $order): self
      {
          return self::sendTo(
              $user,
              'Digital Product Confirmed Successfully',
              'You confirmed receipt and submitted your review. Payment has now been released to the creator.',
              self::TYPE_DIGITAL_PRODUCT_CONFIRMED_BUYER,
              ['product_id' => $order->product->id, 'order_id' => $order->id]
          );
      }
 }
