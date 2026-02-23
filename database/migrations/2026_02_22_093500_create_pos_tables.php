<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Create tax_rates table (no dependencies)
        if (!Schema::hasTable('tax_rates')) {
            Schema::create('tax_rates', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('code')->unique();
                $table->string('description')->nullable();
                $table->decimal('rate', 5, 2);
                $table->enum('rate_type', ['percentage', 'fixed'])->default('percentage');
                $table->decimal('fixed_amount', 10, 2)->nullable();
                $table->string('country')->nullable();
                $table->string('state')->nullable();
                $table->string('city')->nullable();
                $table->string('zip_code')->nullable();
                $table->enum('tax_type', ['sales', 'vat', 'gst', 'service', 'local', 'other'])->default('sales');
                $table->boolean('is_compound')->default(false);
                $table->boolean('is_default')->default(false);
                $table->boolean('applies_to_services')->default(true);
                $table->boolean('applies_to_parts')->default(true);
                $table->boolean('applies_to_labor')->default(true);
                $table->boolean('applies_to_shipping')->default(false);
                $table->text('exemption_categories')->nullable();
                $table->decimal('minimum_amount', 10, 2)->nullable();
                $table->decimal('maximum_amount', 10, 2)->nullable();
                $table->date('effective_from')->nullable();
                $table->date('effective_to')->nullable();
                $table->string('tax_authority')->nullable();
                $table->string('authority_code')->nullable();
                $table->boolean('requires_tax_id')->default(false);
                $table->boolean('is_active')->default(true);
                $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
                $table->foreignId('updated_by')->nullable()->constrained('users')->onDelete('set null');
                $table->timestamps();
                $table->softDeletes();
            });
        }

        // 2. Create discounts table (no dependencies)
        if (!Schema::hasTable('discounts')) {
            Schema::create('discounts', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('code')->unique()->nullable();
                $table->string('description')->nullable();
                $table->enum('type', ['percentage', 'fixed', 'free_shipping', 'buy_x_get_y'])->default('percentage');
                $table->decimal('value', 10, 2);
                $table->decimal('minimum_purchase', 10, 2)->nullable();
                $table->decimal('maximum_discount', 10, 2)->nullable();
                $table->enum('apply_to', ['all', 'services', 'parts', 'labor', 'specific_categories', 'specific_items'])->default('all');
                $table->text('applicable_categories')->nullable();
                $table->text('applicable_items')->nullable();
                $table->text('excluded_items')->nullable();
                $table->boolean('for_new_customers_only')->default(false);
                $table->boolean('for_existing_customers_only')->default(false);
                $table->text('customer_groups')->nullable();
                $table->text('specific_customers')->nullable();
                $table->integer('usage_limit')->nullable();
                $table->integer('usage_limit_per_customer')->nullable();
                $table->integer('times_used')->default(0);
                $table->date('start_date')->nullable();
                $table->date('end_date')->nullable();
                $table->time('start_time')->nullable();
                $table->time('end_time')->nullable();
                $table->text('valid_days')->nullable();
                $table->boolean('can_combine_with_other_discounts')->default(false);
                $table->text('combinable_with')->nullable();
                $table->integer('minimum_items')->nullable();
                $table->integer('minimum_services')->nullable();
                $table->integer('minimum_parts')->nullable();
                $table->integer('buy_quantity')->nullable();
                $table->integer('get_quantity')->nullable();
                $table->decimal('get_discount_percentage', 5, 2)->nullable();
                $table->boolean('is_active')->default(true);
                $table->boolean('is_automatic')->default(false);
                $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
                $table->foreignId('updated_by')->nullable()->constrained('users')->onDelete('set null');
                $table->timestamps();
                $table->softDeletes();
            });
        }

        // 3. Create payment_methods table (no dependencies)
        if (!Schema::hasTable('payment_methods')) {
            Schema::create('payment_methods', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('code')->unique();
                $table->string('description')->nullable();
                $table->string('icon')->nullable();
                $table->string('color')->nullable();
                $table->enum('type', ['cash', 'card', 'bank', 'digital', 'check', 'other'])->default('cash');
                $table->enum('category', ['point_of_sale', 'online', 'mobile', 'manual'])->default('point_of_sale');
                $table->boolean('requires_processing')->default(false);
                $table->boolean('requires_verification')->default(false);
                $table->boolean('requires_reconciliation')->default(false);
                $table->boolean('supports_refunds')->default(true);
                $table->boolean('supports_partial_payments')->default(true);
                $table->decimal('processing_fee_percentage', 5, 2)->default(0);
                $table->decimal('processing_fee_fixed', 10, 2)->default(0);
                $table->decimal('minimum_fee', 10, 2)->nullable();
                $table->decimal('maximum_fee', 10, 2)->nullable();
                $table->integer('settlement_days')->default(0);
                $table->enum('settlement_type', ['daily', 'weekly', 'monthly', 'instant'])->default('daily');
                $table->decimal('minimum_amount', 10, 2)->nullable();
                $table->decimal('maximum_amount', 10, 2)->nullable();
                $table->decimal('daily_limit', 10, 2)->nullable();
                $table->decimal('transaction_limit', 10, 2)->nullable();
                $table->string('gateway_name')->nullable();
                $table->string('gateway_api_key')->nullable();
                $table->string('gateway_secret_key')->nullable();
                $table->string('gateway_merchant_id')->nullable();
                $table->string('gateway_mode')->nullable();
                $table->text('gateway_configuration')->nullable();
                $table->string('bank_name')->nullable();
                $table->string('bank_account_name')->nullable();
                $table->string('bank_account_number')->nullable();
                $table->string('bank_routing_number')->nullable();
                $table->string('bank_swift_code')->nullable();
                $table->string('bank_iban')->nullable();
                $table->boolean('requires_check_number')->default(false);
                $table->boolean('requires_bank_name')->default(false);
                $table->string('wallet_provider')->nullable();
                $table->string('wallet_account_id')->nullable();
                $table->boolean('is_visible')->default(true);
                $table->boolean('is_default')->default(false);
                $table->integer('sort_order')->default(0);
                $table->text('instructions')->nullable();
                $table->boolean('is_active')->default(true);
                $table->boolean('is_online')->default(false);
                $table->boolean('requires_cvv')->default(false);
                $table->boolean('requires_zip_code')->default(false);
                $table->boolean('supports_tokenization')->default(false);
                $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
                $table->foreignId('updated_by')->nullable()->constrained('users')->onDelete('set null');
                $table->timestamps();
                $table->softDeletes();
            });
        }

        // 4. Create invoice_templates table (no dependencies)
        if (!Schema::hasTable('invoice_templates')) {
            Schema::create('invoice_templates', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('code')->unique();
                $table->string('description')->nullable();
                $table->enum('type', ['invoice', 'estimate', 'receipt', 'proforma', 'credit_note'])->default('invoice');
                $table->enum('format', ['standard', 'detailed', 'simplified', 'custom'])->default('standard');
                $table->string('header_html')->nullable();
                $table->string('footer_html')->nullable();
                $table->text('body_html');
                $table->text('css_styles')->nullable();
                $table->boolean('show_company_logo')->default(true);
                $table->string('logo_position')->default('left');
                $table->boolean('show_company_name')->default(true);
                $table->boolean('show_company_address')->default(true);
                $table->boolean('show_company_contact')->default(true);
                $table->boolean('show_company_tax_id')->default(true);
                $table->boolean('show_customer_name')->default(true);
                $table->boolean('show_customer_address')->default(true);
                $table->boolean('show_customer_contact')->default(true);
                $table->boolean('show_customer_tax_id')->default(false);
                $table->boolean('show_invoice_number')->default(true);
                $table->boolean('show_invoice_date')->default(true);
                $table->boolean('show_due_date')->default(true);
                $table->boolean('show_payment_terms')->default(true);
                $table->boolean('show_po_number')->default(false);
                $table->boolean('show_item_description')->default(true);
                $table->boolean('show_item_quantity')->default(true);
                $table->boolean('show_item_unit_price')->default(true);
                $table->boolean('show_item_total')->default(true);
                $table->boolean('show_item_tax')->default(true);
                $table->boolean('show_item_discount')->default(true);
                $table->boolean('show_subtotal')->default(true);
                $table->boolean('show_tax_summary')->default(true);
                $table->boolean('show_discount_summary')->default(true);
                $table->boolean('show_shipping')->default(false);
                $table->boolean('show_total')->default(true);
                $table->boolean('show_amount_paid')->default(true);
                $table->boolean('show_balance_due')->default(true);
                $table->boolean('show_payment_methods')->default(true);
                $table->boolean('show_bank_details')->default(false);
                $table->text('payment_instructions')->nullable();
                $table->boolean('show_notes')->default(true);
                $table->boolean('show_terms')->default(true);
                $table->text('default_notes')->nullable();
                $table->text('default_terms')->nullable();
                $table->boolean('show_vehicle_details')->default(true);
                $table->boolean('show_vehicle_make_model')->default(true);
                $table->boolean('show_vehicle_year')->default(true);
                $table->boolean('show_vehicle_vin')->default(false);
                $table->boolean('show_vehicle_license')->default(false);
                $table->boolean('show_vehicle_mileage')->default(false);
                $table->boolean('show_service_type')->default(true);
                $table->boolean('show_service_date')->default(true);
                $table->boolean('show_technician_name')->default(false);
                $table->boolean('show_warranty_details')->default(true);
                $table->boolean('show_warranty_period')->default(true);
                $table->boolean('show_warranty_terms')->default(false);
                $table->string('primary_color')->default('#007bff');
                $table->string('secondary_color')->default('#6c757d');
                $table->string('font_family')->default('Arial, sans-serif');
                $table->integer('font_size')->default(12);
                $table->enum('paper_size', ['a4', 'letter', 'legal', 'a5'])->default('a4');
                $table->enum('orientation', ['portrait', 'landscape'])->default('portrait');
                $table->decimal('margin_top', 5, 2)->default(1.0);
                $table->decimal('margin_bottom', 5, 2)->default(1.0);
                $table->decimal('margin_left', 5, 2)->default(1.0);
                $table->decimal('margin_right', 5, 2)->default(1.0);
                $table->boolean('is_active')->default(true);
                $table->boolean('is_default')->default(false);
                $table->integer('times_used')->default(0);
                $table->timestamp('last_used_at')->nullable();
                $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
                $table->foreignId('updated_by')->nullable()->constrained('users')->onDelete('set null');
                $table->timestamps();
                $table->softDeletes();
            });
        }

        // 5. Create invoices table (depends on discounts, customers, vehicles, work_orders, appointments)
        if (!Schema::hasTable('invoices')) {
            Schema::create('invoices', function (Blueprint $table) {
                $table->id();
                $table->string('invoice_number')->unique();
                $table->string('reference_number')->nullable();
                $table->enum('invoice_type', ['service', 'parts', 'combined', 'estimate', 'deposit'])->default('service');
                $table->foreignId('customer_id')->constrained('customers')->onDelete('cascade');
                $table->foreignId('vehicle_id')->nullable()->constrained('vehicles')->onDelete('set null');
                $table->foreignId('work_order_id')->nullable()->constrained('work_orders')->onDelete('set null');
                $table->foreignId('appointment_id')->nullable()->constrained('appointments')->onDelete('set null');
                $table->date('invoice_date');
                $table->date('due_date')->nullable();
                $table->date('paid_date')->nullable();
                $table->text('notes')->nullable();
                $table->text('terms')->nullable();
                $table->decimal('subtotal', 10, 2)->default(0);
                $table->decimal('tax_amount', 10, 2)->default(0);
                $table->decimal('discount_amount', 10, 2)->default(0);
                $table->decimal('shipping_amount', 10, 2)->default(0);
                $table->decimal('deposit_amount', 10, 2)->default(0);
                $table->decimal('total_amount', 10, 2)->default(0);
                $table->decimal('amount_paid', 10, 2)->default(0);
                $table->decimal('balance_due', 10, 2)->default(0);
                $table->decimal('tax_rate', 5, 2)->default(0);
                $table->boolean('is_taxable')->default(true);
                $table->foreignId('discount_id')->nullable()->constrained('discounts')->onDelete('set null');
                $table->string('discount_type')->nullable();
                $table->decimal('discount_value', 10, 2)->nullable();
                $table->enum('status', ['draft', 'sent', 'viewed', 'partial', 'paid', 'overdue', 'cancelled', 'refunded'])->default('draft');
                $table->enum('payment_status', ['pending', 'partial', 'paid', 'failed', 'refunded'])->default('pending');
                $table->boolean('is_recurring')->default(false);
                $table->string('recurring_frequency')->nullable();
                $table->date('next_invoice_date')->nullable();
                $table->enum('delivery_method', ['email', 'sms', 'print', 'portal'])->default('email');
                $table->timestamp('sent_at')->nullable();
                $table->timestamp('viewed_at')->nullable();
                $table->timestamp('reminder_sent_at')->nullable();
                $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
                $table->foreignId('updated_by')->nullable()->constrained('users')->onDelete('set null');
                $table->foreignId('approved_by')->nullable()->constrained('users')->onDelete('set null');
                $table->timestamp('approved_at')->nullable();
                $table->softDeletes();
                $table->timestamps();
            });
        }

        // 6. Create invoice_items table (depends on invoices)
        if (!Schema::hasTable('invoice_items')) {
            Schema::create('invoice_items', function (Blueprint $table) {
                $table->id();
                $table->foreignId('invoice_id')->constrained('invoices')->onDelete('cascade');
                $table->string('item_type')->default('service');
                $table->string('item_code')->nullable();
                $table->string('item_name');
                $table->text('description')->nullable();
                $table->decimal('quantity', 10, 2)->default(1);
                $table->string('unit')->nullable();
                $table->decimal('unit_price', 10, 2)->default(0);
                $table->decimal('cost_price', 10, 2)->nullable();
                $table->decimal('discount_percentage', 5, 2)->default(0);
                $table->decimal('discount_amount', 10, 2)->default(0);
                $table->decimal('tax_percentage', 5, 2)->default(0);
                $table->decimal('tax_amount', 10, 2)->default(0);
                $table->decimal('total_amount', 10, 2)->default(0);
                $table->foreignId('service_id')->nullable()->constrained('service_records')->onDelete('set null');
                $table->foreignId('part_id')->nullable()->constrained('inventory')->onDelete('set null');
                $table->foreignId('labor_id')->nullable()->constrained('work_order_tasks')->onDelete('set null');
                $table->foreignId('technician_id')->nullable()->constrained('users')->onDelete('set null');
                $table->boolean('has_warranty')->default(false);
                $table->integer('warranty_months')->nullable();
                $table->date('warranty_start_date')->nullable();
                $table->date('warranty_end_date')->nullable();
                $table->decimal('commission_rate', 5, 2)->nullable();
                $table->decimal('commission_amount', 10, 2)->nullable();
                $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
                $table->integer('sort_order')->default(0);
                $table->timestamps();
            });
        }

        // 7. Create payments table (depends on invoices, payment_methods)
        if (!Schema::hasTable('payments')) {
            Schema::create('payments', function (Blueprint $table) {
                $table->id();
                $table->string('payment_number')->unique();
                $table->string('reference_number')->nullable();
                $table->enum('payment_type', ['invoice', 'deposit', 'advance', 'refund', 'adjustment'])->default('invoice');
                $table->foreignId('invoice_id')->nullable()->constrained('invoices')->onDelete('cascade');
                $table->foreignId('customer_id')->constrained('customers')->onDelete('cascade');
                $table->foreignId('work_order_id')->nullable()->constrained('work_orders')->onDelete('set null');
                $table->date('payment_date');
                $table->decimal('amount', 10, 2);
                $table->decimal('processing_fee', 10, 2)->default(0);
                $table->decimal('net_amount', 10, 2);
                $table->string('currency')->default('PHP');
                $table->decimal('exchange_rate', 10, 4)->default(1);
                $table->foreignId('payment_method_id')->nullable()->constrained('payment_methods')->onDelete('set null');
                $table->string('payment_method_name');
                $table->enum('payment_method_type', ['cash', 'credit_card', 'debit_card', 'check', 'bank_transfer', 'online', 'wallet', 'other'])->default('cash');
                $table->string('card_last_four', 4)->nullable();
                $table->string('card_brand')->nullable();
                $table->string('card_expiry')->nullable();
                $table->string('check_number')->nullable();
                $table->string('check_bank')->nullable();
                $table->date('check_date')->nullable();
                $table->string('bank_name')->nullable();
                $table->string('bank_account')->nullable();
                $table->string('bank_reference')->nullable();
                $table->string('gateway_name')->nullable();
                $table->string('gateway_transaction_id')->nullable();
                $table->string('gateway_response')->nullable();
                $table->enum('status', ['pending', 'processing', 'completed', 'failed', 'cancelled', 'refunded'])->default('pending');
                $table->boolean('is_verified')->default(false);
                $table->boolean('is_reconciled')->default(false);
                $table->text('notes')->nullable();
                $table->string('attachment_path')->nullable();
                $table->foreignId('received_by')->nullable()->constrained('users')->onDelete('set null');
                $table->foreignId('verified_by')->nullable()->constrained('users')->onDelete('set null');
                $table->timestamp('verified_at')->nullable();
                $table->foreignId('reconciled_by')->nullable()->constrained('users')->onDelete('set null');
                $table->timestamp('reconciled_at')->nullable();
                $table->softDeletes();
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
        Schema::dropIfExists('invoice_items');
        Schema::dropIfExists('invoices');
        Schema::dropIfExists('invoice_templates');
        Schema::dropIfExists('payment_methods');
        Schema::dropIfExists('discounts');
        Schema::dropIfExists('tax_rates');
    }
};