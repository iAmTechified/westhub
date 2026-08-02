<x-layouts.app :seo="$seo ?? null">
    <div class="bg-[#F8FBFD] py-20 lg:py-24">
        <div class="container mx-auto px-4 md:px-8 max-w-7xl">
            <div class="max-w-5xl mx-auto mb-12">
                <h1 class="text-4xl md:text-5xl font-display font-bold text-primary-300 mb-4">Application Form</h1>
                <p class="text-neutral-500 text-lg">Fill the form below to join Westhub Healthcare armies of skilled nursing and Non-Nursing Professionals</p>
            </div>

            <div class="max-w-5xl mx-auto" data-join-request-modal data-is-page="true">
                <form class="" data-join-form method="POST" action="{{ route('join-requests.store') }}" enctype="multipart/form-data" novalidate>
                    @csrf

                    <div class="join-form-message" data-form-message aria-live="polite"></div>
                    
                    {{-- Set the default value to skilled unless overridden by URL --}}
                    <input type="hidden" name="applicant_type" value="{{ request('type', 'skilled') }}" data-applicant-type data-required data-label="Professional type">

                    <div class="join-accordion">
                        <section class="join-section is-active" data-section="basic">
                            <button type="button" class="join-section-head" data-section-toggle="basic" aria-expanded="true">
                                <span class="join-section-number">1</span>
                                <span>Basic Information</span>
                            </button>

                            <div class="join-section-panel" data-section-panel>
                                <div class="join-type-row" aria-label="Professional type">
                                    <button type="button" class="join-type-option {{ request('type', 'skilled') === 'skilled' ? 'is-active' : '' }}" data-type-option="skilled">
                                        <img src="{{ asset('assets/icons/Property 1=Skilled.svg') }}" alt="" aria-hidden="true">
                                        <span>Skilled Professional</span>
                                        <span class="join-type-dot"></span>
                                    </button>

                                    <button type="button" class="join-type-option {{ request('type') === 'non-skilled' ? 'is-active' : '' }}" data-type-option="non-skilled">
                                        <img src="{{ asset('assets/icons/Property 1=Non Skilled.svg') }}" alt="" aria-hidden="true">
                                        <span>Non-skilled Professional</span>
                                        <span class="join-type-dot"></span>
                                    </button>
                                </div>

                                <div class="join-form-grid">
                                    <div class="join-field">
                                        <label>First Name <span>*</span></label>
                                        <div class="join-name-row">
                                            <div class="join-custom-select join-title-select" data-custom-select data-select-options="titles" data-name="title" data-label="Title" data-default="Mr." data-required></div>
                                            <input class="join-input" type="text" name="first_name" placeholder="First name" autocomplete="given-name" data-required data-label="First name">
                                        </div>
                                        <p class="join-error" data-error></p>
                                    </div>

                                    <div class="join-field">
                                        <label>Home Address <span>*</span></label>
                                        <textarea class="join-input join-textarea" name="home_address" autocomplete="street-address" data-required data-label="Home address"></textarea>
                                        <p class="join-error" data-error></p>
                                    </div>

                                    <div class="join-field">
                                        <label>Last Name <span>*</span></label>
                                        <input class="join-input" type="text" name="last_name" placeholder="Last name" autocomplete="family-name" data-required data-label="Last name">
                                        <p class="join-error" data-error></p>
                                    </div>

                                    <div class="join-field">
                                        <label>Position Applied for <span>*</span></label>
                                        <input class="join-input" type="text" name="position_applied_for" data-required data-label="Position applied for">
                                        <p class="join-error" data-error></p>
                                    </div>

                                    <div class="join-field">
                                        <label>M.I</label>
                                        <input class="join-input" type="text" name="middle_initial" maxlength="8" placeholder="Middle name" autocomplete="additional-name" data-label="Middle initial">
                                        <p class="join-error" data-error></p>
                                    </div>

                                    <div class="join-field">
                                        <label>Date Available <span>*</span></label>
                                        <div class="join-date" data-date-picker data-name="date_available" data-label="Date available" data-required></div>
                                        <p class="join-error" data-error></p>
                                    </div>

                                    <div class="join-field">
                                        <label>Phone Number <span>*</span></label>
                                        <div class="join-phone-row">
                                            <div class="join-custom-select join-country-select" data-custom-select data-select-options="countries" data-name="phone_country_code" data-label="Phone country code" data-default="+1" data-required></div>
                                            <input class="join-input" type="tel" name="phone_number" placeholder="(201) 555-0122" autocomplete="tel-national" data-required data-phone data-label="Phone number">
                                        </div>
                                        <p class="join-error" data-error></p>
                                    </div>

                                    <div class="join-field">
                                        <label>Desired Salary/Hour <span>*</span></label>
                                        <div class="join-money-row">
                                            <span>$</span>
                                            <input class="join-input" type="text" name="desired_salary_hour" inputmode="decimal" placeholder="Enter amount" data-required data-number data-label="Desired salary per hour">
                                        </div>
                                        <p class="join-error" data-error></p>
                                    </div>

                                    <div class="join-field">
                                        <label>Email <span>*</span></label>
                                        <input class="join-input" type="email" name="email" placeholder="youremail@mail.com" autocomplete="email" data-required data-label="Email">
                                        <p class="join-error" data-error></p>
                                    </div>
                                </div>

                                <button type="button" class="join-save-button" data-next-section="eligibility">Save and Continue</button>
                            </div>
                        </section>

                        <section class="join-section" data-section="eligibility">
                            <button type="button" class="join-section-head" data-section-toggle="eligibility" aria-expanded="false">
                                <span class="join-section-number">2</span>
                                <span>Eligibility &amp; Work Status</span>
                            </button>

                            <div class="join-section-panel" data-section-panel>
                                <div class="join-form-grid">
                                    <div class="join-field">
                                        <div class="join-radio-group-wrap">
                                            <label>Are you a citizen of the United States? <span>*</span></label>
                                            <div class="join-radio-group" data-radio-group data-name="citizen_us" data-label="U.S. citizenship" data-required>
                                                <button type="button" data-radio-value="yes">Yes</button>
                                                <button type="button" data-radio-value="no">No</button>
                                            </div>
                                        </div>
                                        <p class="join-error" data-error></p>
                                    </div>

                                    <div class="join-field">
                                        <div class="join-radio-group-wrap">
                                            <label>Have you ever been convicted of a felony? <span>*</span></label>
                                            <div class="join-radio-group" data-radio-group data-name="convicted_felony" data-label="Felony conviction status" data-required>
                                                <button type="button" data-radio-value="yes">Yes</button>
                                                <button type="button" data-radio-value="no">No</button>
                                            </div>
                                        </div>
                                        <p class="join-error" data-error></p>
                                    </div>

                                    <div class="join-field">
                                        <div class="join-radio-group-wrap">
                                            <label>If no, are you authorized to work in the U.S.? <span>*</span></label>
                                            <div class="join-radio-group" data-radio-group data-name="authorized_us" data-label="Work authorization" data-required>
                                                <button type="button" data-radio-value="yes">Yes</button>
                                                <button type="button" data-radio-value="no">No</button>
                                            </div>
                                        </div>
                                        <p class="join-error" data-error></p>
                                    </div>

                                    <div class="join-field">
                                        <label>If yes, explain</label>
                                        <input class="join-input" type="text" name="felony_explanation" data-required-if="convicted_felony:yes" data-label="Felony explanation">
                                        <p class="join-error" data-error></p>
                                    </div>

                                    <div class="join-field">
                                        <label>Social Security/TIN No <span>*</span></label>
                                        <input class="join-input" type="text" name="social_security" autocomplete="off" data-required data-label="Social Security/TIN number">
                                        <p class="join-error" data-error></p>
                                    </div>

                                    <div class="join-field">
                                        <div class="join-radio-group-wrap">
                                            <label>Have you ever worked for this company? <span>*</span></label>
                                            <div class="join-radio-group" data-radio-group data-name="worked_here" data-label="Previous company employment" data-required>
                                                <button type="button" data-radio-value="yes">Yes</button>
                                                <button type="button" data-radio-value="no">No</button>
                                            </div>
                                        </div>
                                        <p class="join-error" data-error></p>
                                    </div>

                                    <div class="join-field">
                                        <label>Date of Birth <span>*</span></label>
                                        <div class="join-date" data-date-picker data-name="date_of_birth" data-label="Date of birth" data-required data-date-role="dob"></div>
                                        <p class="join-error" data-error></p>
                                    </div>

                                    <div class="join-field">
                                        <label>If yes when?</label>
                                        <input class="join-input" type="text" name="worked_here_when" data-required-if="worked_here:yes" data-label="Previous employment date">
                                        <p class="join-error" data-error></p>
                                    </div>
                                </div>

                                <button type="button" class="join-save-button" data-next-section="professional">Save and Continue</button>
                            </div>
                        </section>

                        <section class="join-section" data-section="professional">
                            <button type="button" class="join-section-head" data-section-toggle="professional" aria-expanded="false">
                                <span class="join-section-number">3</span>
                                <span>Professional Background</span>
                            </button>

                            <div class="join-section-panel" data-section-panel>
                                <h4 class="join-subtitle">Education</h4>
                                <div class="join-two-column-blocks">
                                    @foreach([1, 2] as $educationNumber)
                                        @php($educationIndex = $educationNumber - 1)
                                        <div class="join-field-stack">
                                            <h4 class="join-column-title">Education {{ $educationNumber }}</h4>
                                            <div class="join-field">
                                                <label>School Name <span>*</span></label>
                                                <input class="join-input" type="text" name="education[{{ $educationIndex }}][school_name]" data-required data-label="Education {{ $educationNumber }} school name">
                                                <p class="join-error" data-error></p>
                                            </div>
                                            <div class="join-field">
                                                <label>Address <span>*</span></label>
                                                <input class="join-input" type="text" name="education[{{ $educationIndex }}][address]" data-required data-label="Education {{ $educationNumber }} address">
                                                <p class="join-error" data-error></p>
                                            </div>
                                            <div class="join-field">
                                                <label>Degree <span>*</span></label>
                                                <input class="join-input" type="text" name="education[{{ $educationIndex }}][degree]" data-required data-label="Education {{ $educationNumber }} degree">
                                                <p class="join-error" data-error></p>
                                            </div>
                                            <div class="join-field">
                                                <label>From <span>*</span></label>
                                                <div class="join-date" data-date-picker data-name="education[{{ $educationIndex }}][from]" data-label="Education {{ $educationNumber }} start date" data-required></div>
                                                <p class="join-error" data-error></p>
                                            </div>
                                            <div class="join-field">
                                                <label>To <span>*</span></label>
                                                <div class="join-date" data-date-picker data-name="education[{{ $educationIndex }}][to]" data-label="Education {{ $educationNumber }} end date" data-required></div>
                                                <p class="join-error" data-error></p>
                                            </div>
                                            <div class="join-field">
                                                <div class="join-radio-group-wrap">
                                                    <label>Did you Graduate? <span>*</span></label>
                                                    <div class="join-radio-group" data-radio-group data-name="education[{{ $educationIndex }}][graduated]" data-label="Education {{ $educationNumber }} graduation status" data-required>
                                                        <button type="button" data-radio-value="yes">Yes</button>
                                                        <button type="button" data-radio-value="no">No</button>
                                                    </div>
                                                </div>
                                                <p class="join-error" data-error></p>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>

                                <h4 class="join-subtitle join-subtitle-spaced">Military Service</h4>
                                <div class="join-form-grid">
                                    <div class="join-field">
                                        <label>Branch</label>
                                        <input class="join-input" type="text" name="military_branch" data-label="Military branch">
                                        <p class="join-error" data-error></p>
                                    </div>
                                    <div class="join-field">
                                        <label>Rank at Discharge</label>
                                        <input class="join-input" type="text" name="military_rank" data-label="Military rank at discharge">
                                        <p class="join-error" data-error></p>
                                    </div>
                                    <div class="join-field">
                                        <label>From</label>
                                        <div class="join-date" data-date-picker data-name="military_from" data-label="Military service start date"></div>
                                        <p class="join-error" data-error></p>
                                    </div>
                                    <div class="join-field">
                                        <label>Type of Discharge</label>
                                        <input class="join-input" type="text" name="military_discharge_type" data-label="Military discharge type">
                                        <p class="join-error" data-error></p>
                                    </div>
                                    <div class="join-field">
                                        <label>To</label>
                                        <div class="join-date" data-date-picker data-name="military_to" data-label="Military service end date"></div>
                                        <p class="join-error" data-error></p>
                                    </div>
                                </div>

                                <button type="button" class="join-save-button" data-next-section="experience">Save and Continue</button>
                            </div>
                        </section>

                        <section class="join-section" data-section="experience">
                            <button type="button" class="join-section-head" data-section-toggle="experience" aria-expanded="false">
                                <span class="join-section-number">4</span>
                                <span>Work Experience &amp; References</span>
                            </button>

                            <div class="join-section-panel" data-section-panel>
                                <div class="join-two-column-blocks">
                                    @foreach([1, 2] as $employerNumber)
                                        @php($employerIndex = $employerNumber - 1)
                                        <div class="join-field-stack">
                                            <h4 class="join-column-title">Previous Employment {{ $employerNumber }}</h4>
                                            <div class="join-field">
                                                <label>Company <span>*</span></label>
                                                <input class="join-input" type="text" name="employers[{{ $employerIndex }}][company]" data-required data-label="Previous employer {{ $employerNumber }} company">
                                                <p class="join-error" data-error></p>
                                            </div>
                                            <div class="join-field">
                                                <label>Phone Number <span>*</span></label>
                                                <div class="join-phone-row">
                                                    <div class="join-custom-select join-country-select" data-custom-select data-select-options="countries" data-name="employers[{{ $employerIndex }}][phone_country_code]" data-label="Previous employer {{ $employerNumber }} phone country code" data-default="+1" data-required></div>
                                                    <input class="join-input" type="tel" name="employers[{{ $employerIndex }}][phone_number]" placeholder="(201) 555-0122" data-required data-phone data-label="Previous employer {{ $employerNumber }} phone number">
                                                </div>
                                                <p class="join-error" data-error></p>
                                            </div>
                                            <div class="join-field">
                                                <label>Address <span>*</span></label>
                                                <input class="join-input" type="text" name="employers[{{ $employerIndex }}][address]" data-required data-label="Previous employer {{ $employerNumber }} address">
                                                <p class="join-error" data-error></p>
                                            </div>
                                            <div class="join-field">
                                                <label>Supervisor <span>*</span></label>
                                                <input class="join-input" type="text" name="employers[{{ $employerIndex }}][supervisor]" data-required data-label="Previous employer {{ $employerNumber }} supervisor">
                                                <p class="join-error" data-error></p>
                                            </div>
                                            <div class="join-field">
                                                <label>Job Title <span>*</span></label>
                                                <input class="join-input" type="text" name="employers[{{ $employerIndex }}][job_title]" data-required data-label="Previous employer {{ $employerNumber }} job title">
                                                <p class="join-error" data-error></p>
                                            </div>
                                            <div class="join-field">
                                                <label>Responsibilities <span>*</span></label>
                                                <textarea class="join-input join-textarea" name="employers[{{ $employerIndex }}][responsibilities]" data-required data-label="Previous employer {{ $employerNumber }} responsibilities"></textarea>
                                                <p class="join-error" data-error></p>
                                            </div>
                                            <div class="join-field">
                                                <label>From <span>*</span></label>
                                                <div class="join-date" data-date-picker data-name="employers[{{ $employerIndex }}][from]" data-label="Previous employer {{ $employerNumber }} start date" data-required></div>
                                                <p class="join-error" data-error></p>
                                            </div>
                                            <div class="join-field">
                                                <label>To <span>*</span></label>
                                                <div class="join-date" data-date-picker data-name="employers[{{ $employerIndex }}][to]" data-label="Previous employer {{ $employerNumber }} end date" data-required></div>
                                                <p class="join-error" data-error></p>
                                            </div>
                                            <div class="join-field">
                                                <label>Reason for Leaving <span>*</span></label>
                                                <textarea class="join-input join-textarea" name="employers[{{ $employerIndex }}][reason_for_leaving]" data-required data-label="Previous employer {{ $employerNumber }} reason for leaving"></textarea>
                                                <p class="join-error" data-error></p>
                                            </div>
                                            <div class="join-field">
                                                <div class="join-radio-group-wrap">
                                                    <label>May we contact your previous supervisor for a reference? <span>*</span></label>
                                                    <div class="join-radio-group" data-radio-group data-name="employers[{{ $employerIndex }}][can_contact]" data-label="Previous employer {{ $employerNumber }} contact permission" data-required>
                                                        <button type="button" data-radio-value="yes">Yes</button>
                                                        <button type="button" data-radio-value="no">No</button>
                                                    </div>
                                                </div>
                                                <p class="join-error" data-error></p>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>

                                <div class="join-two-column-blocks join-reference-grid">
                                    @foreach([1, 2] as $referenceNumber)
                                        @php($referenceIndex = $referenceNumber - 1)
                                        <div class="join-field-stack">
                                            <h4 class="join-column-title">References {{ $referenceNumber }}</h4>
                                            <div class="join-field">
                                                <label>Reference Full Name <span>*</span></label>
                                                <input class="join-input" type="text" name="references[{{ $referenceIndex }}][full_name]" data-required data-label="Reference {{ $referenceNumber }} full name">
                                                <p class="join-error" data-error></p>
                                            </div>
                                            <div class="join-field">
                                                <label>Relationship <span>*</span></label>
                                                <input class="join-input" type="text" name="references[{{ $referenceIndex }}][relationship]" data-required data-label="Reference {{ $referenceNumber }} relationship">
                                                <p class="join-error" data-error></p>
                                            </div>
                                            <div class="join-field">
                                                <label>Company <span>*</span></label>
                                                <input class="join-input" type="text" name="references[{{ $referenceIndex }}][company]" data-required data-label="Reference {{ $referenceNumber }} company">
                                                <p class="join-error" data-error></p>
                                            </div>
                                            <div class="join-field">
                                                <label>Phone Number <span>*</span></label>
                                                <div class="join-phone-row">
                                                    <div class="join-custom-select join-country-select" data-custom-select data-select-options="countries" data-name="references[{{ $referenceIndex }}][phone_country_code]" data-label="Reference {{ $referenceNumber }} phone country code" data-default="+1" data-required></div>
                                                    <input class="join-input" type="tel" name="references[{{ $referenceIndex }}][phone_number]" placeholder="(201) 555-0122" data-required data-phone data-label="Reference {{ $referenceNumber }} phone number">
                                                </div>
                                                <p class="join-error" data-error></p>
                                            </div>
                                            <div class="join-field">
                                                <label>Address <span>*</span></label>
                                                <input class="join-input" type="text" name="references[{{ $referenceIndex }}][address]" data-required data-label="Reference {{ $referenceNumber }} address">
                                                <p class="join-error" data-error></p>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>

                                <button type="button" class="join-save-button" data-next-section="credentials">Save and Continue</button>
                            </div>
                        </section>

                        <section class="join-section" data-section="credentials">
                            <button type="button" class="join-section-head" data-section-toggle="credentials" aria-expanded="false">
                                <span class="join-section-number">5</span>
                                <span>Credentials</span>
                            </button>

                            <div class="join-section-panel" data-section-panel>
                                <div class="join-credentials-grid">
                                    <div class="join-upload-group">
                                        <label>Upload Resume and Supporting Documents <span>*</span></label>
                                        <div class="join-upload-zone" data-upload-zone>
                                            <input type="file" name="documents[]" multiple accept=".pdf,.doc,.docx,.jpg,.jpeg,.png,.webp" data-upload-input data-required data-label="Resume and supporting documents" data-file-kind="documents">
                                            <div class="join-upload-placeholder" data-upload-placeholder>
                                                <strong>Drag and drop file here</strong>
                                                <span>Or click to browse</span>
                                                <b>Upload Files</b>
                                            </div>
                                            <div class="join-upload-preview" data-upload-preview></div>
                                        </div>
                                        <p class="join-upload-help">Maximum 3 files. Maximum file size: 20MB per file. PDF, DOC, DOCX, JPG, PNG, and WEBP are accepted.</p>
                                        <p class="join-error" data-error></p>
                                    </div>

                                    <div class="join-field-stack">
                                        <div class="join-field">
                                            <label>Date <span>*</span></label>
                                            <div class="join-date" data-date-picker data-name="signature_date" data-label="Signature date" data-required></div>
                                            <p class="join-error" data-error></p>
                                        </div>

                                        <div class="join-upload-group">
                                            <label>Signature <span>*</span></label>
                                            <div class="join-upload-zone join-signature-zone" data-upload-zone>
                                                <input type="file" name="signature_file" accept=".jpg,.jpeg,.png,.webp" data-upload-input data-required data-label="Signature image" data-file-kind="signature">
                                                <div class="join-upload-placeholder" data-upload-placeholder>
                                                    <strong>Drag and drop file here to sign</strong>
                                                    <span>Or click to browse</span>
                                                    <b>Upload Files</b>
                                                </div>
                                                <div class="join-upload-preview" data-upload-preview></div>
                                            </div>
                                            <p class="join-signature-note">By signing this document with an electronic signature, I agree that such signature will be as valid as handwritten signatures to the extent allowed by local law.</p>
                                            <p class="join-error" data-error></p>
                                        </div>

                                        <div class="join-disclaimer-block">
                                            <h4>Disclaimers</h4>
                                            <label class="join-check">
                                                <input type="checkbox" name="disclaimer_truth" value="1" data-required data-label="Truth certification">
                                                <span></span>
                                                <b>I certify that my answers are true and complete to the best of my knowledge.</b>
                                            </label>
                                            <label class="join-check">
                                                <input type="checkbox" name="disclaimer_release" value="1" data-required data-label="Application release acknowledgement">
                                                <span></span>
                                                <b>If this application leads to employment, I understand that false or misleading information in my application or interview may result in my release.</b>
                                            </label>
                                            <p class="join-error" data-error></p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </section>
                    </div>

                    <button type="submit" class="join-submit-button mb-4 mt-8" data-join-submit disabled>Submit Form</button>
                </form>
            </div>
        </div>
    </div>

    {{-- Download Documents Section --}}
    <x-application-form.download-documents />

    {{-- About Sections from the design --}}
    <x-about.intro />
    <x-about.vision-mission />
    <x-about.values-cards />

</x-layouts.app>
