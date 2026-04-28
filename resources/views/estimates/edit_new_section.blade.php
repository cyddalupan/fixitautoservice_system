
                <div class="mb-3">
                    <label for="service_advisor_id" class="form-label">Service Advisor</label>
                    <select name="service_advisor_id" id="service_advisor_id" class="form-select">
                        <option value="">Select Service Advisor</option>
                        @php
                            $advisors = \App\Models\User::where('is_active', true)->whereIn('role', ['service_advisor', 'admin', 'super_admin'])->get();
                        @endphp
                        @foreach($advisors as $advisor)
                            <option value="{{ $advisor->id }}" {{ ($estimate->service_advisor_id ?? old('service_advisor_id')) == $advisor->id ? 'selected' : '' }}>
                                {{ $advisor->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="mb-3">
                    <label for="technician_id" class="form-label">Technician</label>
                    <select name="technician_id" id="technician_id" class="form-select">
                        <option value="">Select Technician</option>
                        @php
                            $technicians = \App\Models\User::where('role', 'technician')->where('is_active', true)->get();
                        @endphp
                        @foreach($technicians as $tech)
                            <option value="{{ $tech->id }}" {{ $estimate->technician_id == $tech->id ? 'selected' : '' }}>
                                {{ $tech->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>
