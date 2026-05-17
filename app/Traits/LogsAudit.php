<?php

namespace App\Traits;

use App\Models\AuditLog;
use Illuminate\Support\Facades\Request;

trait LogsAudit
{
    protected static function bootLogsAudit()
    {
        static::created(function ($model) {
            $model->logAudit('created');
        });

        static::updated(function ($model) {
            $model->logAudit('updated');
        });

        static::deleted(function ($model) {
            $model->logAudit('deleted');
        });
    }

    public function logAudit($action)
    {
        $oldValues = null;
        $newValues = null;

        if ($action === 'created') {
            $newValues = $this->getAttributes();
        } elseif ($action === 'updated') {
            $newValues = $this->getChanges();
            $oldValues = array_intersect_key($this->getOriginal(), $newValues);

            // Special case: detected Archive/Restore
            if (isset($newValues['is_archived'])) {
                $action = $newValues['is_archived'] ? 'archived' : 'restored';
            }
        } elseif ($action === 'deleted') {
            $oldValues = $this->getOriginal();
        }

        // Clean up sensitive or unnecessary fields
        $ignore = ['created_at', 'updated_at', 'deleted_at'];
        if ($oldValues) {
            $oldValues = array_diff_key($oldValues, array_flip($ignore));
        }
        if ($newValues) {
            $newValues = array_diff_key($newValues, array_flip($ignore));
        }

        // If no changes in updated, don't log
        if ($action === 'updated' && empty($newValues)) {
            return;
        }

        // Determine the auditable name
        $auditableName = null;
        if ($this instanceof \App\Models\Tour) {
            $auditableName = $this->name;
        } elseif ($this instanceof \App\Models\TourSchedule) {
            // If deleting, the relation might be gone if we don't eager load it or if it's already detached
            // but for CUD it should generally be available.
            $tourName = $this->tour ? $this->tour->name : 'Unknown Tour';
            $date = \Carbon\Carbon::parse($this->date)->format('M d, Y');
            $auditableName = "Schedule for '{$tourName}' on {$date}";
        } elseif ($this instanceof \App\Models\TourBooking) {
            $guestName = $this->user ? $this->user->name : 'Unknown Guest';
            $tourName = ($this->tourSchedule && $this->tourSchedule->tour) ? $this->tourSchedule->tour->name : 'Unknown Tour';
            $auditableName = "Booking #{$this->id} ({$guestName} - {$tourName})";
        } elseif ($this instanceof \App\Models\Payment) {
            $guestName = ($this->booking && $this->booking->user) ? $this->booking->user->name : 'Unknown Guest';
            $auditableName = "Payment #{$this->id} for {$guestName} (₱" . number_format($this->amount, 2) . ")";
        }

        AuditLog::create([
            'action'         => $action,
            'auditable_type' => get_class($this),
            'auditable_id'   => $this->id,
            'auditable_name' => $auditableName,
            'old_values'     => $oldValues,
            'new_values'     => $newValues,
            'ip_address'     => Request::ip(),
        ]);
    }
}
