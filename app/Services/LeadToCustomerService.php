<?php

namespace App\Services;

use App\Models\Customer;
use App\Models\Vehicle;
use App\Models\Quotation;

class LeadToCustomerService
{
    /**
     * Convert/auto-create customer and vehicle from a quotation submission.
     *
     * Called right after Quotation::create() in storePublic().
     *
     * @param Quotation $quotation
     * @return array [customer, vehicle, was_created]
     */
    public static function processQuotation(Quotation $quotation): array
    {
        // 1. Match existing customer
        $customer = self::findOrCreateCustomer($quotation);

        // 2. Link quotation to customer
        $quotation->customer_id = $customer->id;
        $quotation->save();

        // 3. Create or link vehicle
        $vehicle = self::findOrCreateVehicle($customer, $quotation);
        $quotation->vehicle_id = $vehicle->id;
        $quotation->save();

        return [$customer, $vehicle];
    }

    /**
     * Find existing customer or create a new one.
     */
    protected static function findOrCreateCustomer(Quotation $quotation): Customer
    {
        // Priority 1: Mobile number (most reliable)
        if (!empty($quotation->phone)) {
            $phone = preg_replace('/[^0-9]/', '', $quotation->phone);
            $customer = Customer::whereRaw("REPLACE(REPLACE(REPLACE(phone, ' ', ''), '-', ''), '+', '') LIKE ?", ["%$phone"])
                ->orWhere('phone', $quotation->phone)
                ->first();
            if ($customer) return $customer;
        }

        // Priority 2: Email
        if (!empty($quotation->email)) {
            $customer = Customer::where('email', $quotation->email)->first();
            if ($customer) return $customer;
        }

        // Priority 3: Full name + vehicle (fuzzy)
        if (!empty($quotation->name) && !empty($quotation->vehicle_make)) {
            $nameParts = explode(' ', $quotation->name, 2);
            $firstName = $nameParts[0];
            $lastName = $nameParts[1] ?? '';

            $customer = Customer::where('first_name', 'LIKE', $firstName)
                ->where('last_name', 'LIKE', "%$lastName%")
                ->whereHas('vehicles', function ($q) use ($quotation) {
                    $q->where('make', $quotation->vehicle_make);
                })
                ->first();
            if ($customer) return $customer;
        }

        // Not found — create new customer
        $nameParts = explode(' ', $quotation->name, 2);

        return Customer::create([
            'first_name'       => $nameParts[0],
            'last_name'        => $nameParts[1] ?? $nameParts[0],
            'email'            => $quotation->email,
            'phone'            => $quotation->phone,
            'address'          => $quotation->address ?? '',
            'city'             => $quotation->city ?? '',
            'state'            => $quotation->barangay ?? '',
            'notes'            => 'Lead from Quotation Form. Concern: ' . ($quotation->service_description ?? ''),
            'is_active'        => true,
            'customer_type'    => 'individual',
        ]);
    }

    /**
     * Find existing vehicle or create a new one for the customer.
     */
    protected static function findOrCreateVehicle(Customer $customer, Quotation $quotation): Vehicle
    {
        // Try by license plate
        if (!empty($quotation->license_plate)) {
            $vehicle = Vehicle::where('license_plate', $quotation->license_plate)
                ->where('customer_id', $customer->id)
                ->first();
            if ($vehicle) return $vehicle;
        }

        // Try by VIN
        if (!empty($quotation->vin_number)) {
            $vehicle = Vehicle::where('vin', $quotation->vin_number)->first();
            if ($vehicle) {
                // Reassign to this customer if orphaned
                if ($vehicle->customer_id !== $customer->id) {
                    $vehicle->customer_id = $customer->id;
                    $vehicle->save();
                }
                return $vehicle;
            }
        }

        // Try by make + model + year
        if (!empty($quotation->vehicle_make) && !empty($quotation->vehicle_model)) {
            $vehicle = Vehicle::where('customer_id', $customer->id)
                ->where('make', $quotation->vehicle_make)
                ->where('model', $quotation->vehicle_model)
                ->where('year', $quotation->vehicle_year)
                ->first();
            if ($vehicle) return $vehicle;
        }

        // Create new vehicle
        $notes = 'Lead from Quotation Form';
        if (!empty($quotation->service_description)) {
            $notes .= '. Concern: ' . $quotation->service_description;
        }

        return Vehicle::create([
            'customer_id'          => $customer->id,
            'license_plate'        => $quotation->license_plate ?? '',
            'make'                 => $quotation->vehicle_make,
            'model'                => $quotation->vehicle_model,
            'year'                 => $quotation->vehicle_year,
            'color'                => $quotation->color ?? '',
            'engine_type'          => $quotation->engine_type ?? '',
            'transmission'         => $quotation->transmission ?? '',
            'vin'                  => $quotation->vin_number ?? '',
            'odometer'             => $quotation->mileage ?? 0,
            'notes'                => $notes,
            'is_active'            => true,
        ]);
    }

    /**
     * Convert manually — admin clicks "Convert to Customer" on a quotation.
     */
    public static function convertLead(Quotation $quotation): array
    {
        [$customer, $vehicle] = self::processQuotation($quotation);

        $quotation->status = 'converted_to_customer';
        $quotation->save();

        // Add a service record referencing the concern
        if (!empty($quotation->service_description)) {
            $customer->serviceRecords()->create([
                'vehicle_id'     => $vehicle->id,
                'service_date'   => now(),
                'service_type'   => 'Initial Consultation',
                'description'    => 'Lead from Quotation Form. Concern: ' . $quotation->service_description,
                'diagnosis'      => $quotation->service_description,
                'service_status' => 'pending',
            ]);
        }

        return [$customer, $vehicle];
    }
}
