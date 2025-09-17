<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class WebhookPaymentRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     * PRODUCTION-GRADE validation for massive scale webhook processing
     */
    public function rules(): array
    {
        return [
            // CRITICAL: Transaction ID validation for idempotency
            'txn_id' => [
                'required',
                'string',
                'max:255',
                'regex:/^[a-zA-Z0-9_-]+$/', // Alphanumeric with underscores and hyphens
            ],
            
            // CRITICAL: Order UUID validation
            'order_uuid' => [
                'required',
                'uuid',
            ],
            
            // CRITICAL: Status validation with all possible states
            'status' => [
                'required',
                Rule::in(['pending', 'paid', 'failed', 'refunded']),
            ],
            
            // CRITICAL: Amount validation with realistic limits
            'amount' => [
                'required',
                'numeric',
                'min:0.01', // Minimum 1 cent
                'max:999999.99', // Maximum $999,999.99
                'regex:/^\d+(\.\d{1,2})?$/', // Decimal with max 2 places
            ],
            
            // CRITICAL: Timestamp validation
            'timestamp' => [
                'required',
                'date',
                'before_or_equal:now', // Cannot be in the future
                'after:2020-01-01', // Reasonable date range
            ],
            
            // CRITICAL: Metadata validation
            'metadata' => [
                'nullable',
                'array',
                'max:50', // Limit number of metadata keys
            ],
            
            // CRITICAL: Metadata key validation - allow any type
            'metadata.*' => [
                'nullable',
                'max:1000', // Limit metadata value length
            ],
            
            // OPTIONAL: Webhook source identification
            'webhook_source' => [
                'nullable',
                'string',
                'max:50',
                Rule::in(['stripe', 'paypal', 'square', 'unknown']),
            ],
            
            // OPTIONAL: Correlation ID for distributed tracing
            'correlation_id' => [
                'nullable',
                'string',
                'max:255',
                'regex:/^[a-zA-Z0-9_-]+$/',
            ],
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'txn_id.required' => 'Transaction ID is required for idempotency.',
            'txn_id.string' => 'Transaction ID must be a string.',
            'txn_id.regex' => 'Transaction ID contains invalid characters.',
            'txn_id.max' => 'Transaction ID cannot exceed 255 characters.',
            
            'order_uuid.required' => 'Order UUID is required.',
            'order_uuid.uuid' => 'Order UUID must be a valid UUID format.',
            
            'status.required' => 'Payment status is required.',
            'status.in' => 'Status must be one of: pending, paid, failed, refunded.',
            
            'amount.required' => 'Amount is required.',
            'amount.numeric' => 'Amount must be a valid number.',
            'amount.min' => 'Amount must be at least $0.01.',
            'amount.max' => 'Amount cannot exceed $999,999.99.',
            'amount.regex' => 'Amount must have at most 2 decimal places.',
            
            'timestamp.required' => 'Timestamp is required.',
            'timestamp.date' => 'Timestamp must be a valid date.',
            'timestamp.before_or_equal' => 'Timestamp cannot be in the future.',
            'timestamp.after' => 'Timestamp must be after 2020-01-01.',
            
            'metadata.array' => 'Metadata must be an array.',
            'metadata.max' => 'Metadata cannot have more than 50 keys.',
            'metadata.*.max' => 'Metadata values cannot exceed 1000 characters.',
            
            'webhook_source.string' => 'Webhook source must be a string.',
            'webhook_source.max' => 'Webhook source cannot exceed 50 characters.',
            'webhook_source.in' => 'Webhook source must be one of: stripe, paypal, square, unknown.',
            
            'correlation_id.string' => 'Correlation ID must be a string.',
            'correlation_id.max' => 'Correlation ID cannot exceed 255 characters.',
            'correlation_id.regex' => 'Correlation ID contains invalid characters.',
        ];
    }

    /**
     * Prepare the data for validation.
     * PRODUCTION-GRADE data sanitization
     */
    protected function prepareForValidation(): void
    {
        // CRITICAL: Sanitize transaction ID
        if ($this->has('txn_id')) {
            $this->merge([
                'txn_id' => trim($this->input('txn_id'))
            ]);
        }

        // CRITICAL: Sanitize order UUID
        if ($this->has('order_uuid')) {
            $this->merge([
                'order_uuid' => strtolower(trim($this->input('order_uuid')))
            ]);
        }

        // CRITICAL: Sanitize status
        if ($this->has('status')) {
            $this->merge([
                'status' => strtolower(trim($this->input('status')))
            ]);
        }

        // CRITICAL: Sanitize webhook source
        if ($this->has('webhook_source')) {
            $this->merge([
                'webhook_source' => strtolower(trim($this->input('webhook_source')))
            ]);
        }

        // CRITICAL: Sanitize correlation ID
        if ($this->has('correlation_id')) {
            $this->merge([
                'correlation_id' => trim($this->input('correlation_id'))
            ]);
        }
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'txn_id' => 'transaction ID',
            'order_uuid' => 'order UUID',
            'webhook_source' => 'webhook source',
            'correlation_id' => 'correlation ID',
        ];
    }
}
