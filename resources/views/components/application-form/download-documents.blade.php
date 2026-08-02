@php
$documents = [
    ['name' => 'Employment Application Form', 'file' => 'Employment Application.pdf'],
    ['name' => 'RN Job Description', 'file' => 'RN Job Description.pdf'],
    ['name' => 'CNA Job Description', 'file' => 'CNA Job Description.pdf'],
    ['name' => 'LPN Job Description', 'file' => 'LPN Job Description.pdf'],
    ['name' => 'Standard of Conduct', 'file' => 'Standard of Conduct.pdf'],
    ['name' => 'Attestation to Guidelines for Nurses Working in Home Care', 'file' => 'Attestation to Guidlines.pdf'],
    ['name' => 'Conflict of Interest Statement', 'file' => 'Conflict of Interest Statement.pdf'],
    ['name' => 'Employee HandBook', 'file' => 'Employee Handbook.pdf'],
    ['name' => 'Employer & Employee Agreement', 'file' => 'Employer & Employee Agreement.pdf'],
    ['name' => 'Request or Decline a Hepatitis B Vaccine', 'file' => 'Request or Decliine a Hepatitis B Vaccine.pdf'],
];
@endphp

<section class="py-20 lg:py-24 bg-white">
    <div class="container mx-auto px-4 md:px-8 max-w-7xl">
        <div class="mb-12">
            <h2 class="text-4xl md:text-5xl font-display font-bold text-[#1C3F78] mb-4">Download Documents</h2>
            <p class="text-[#64748B] text-lg">Alternatively download the documents below and submit to appropriate authority</p>
        </div>

        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-6 lg:gap-8">
            @foreach($documents as $index => $doc)
            <a href="{{ asset('assets/docs/' . $doc['file']) }}" download="{{ $doc['file'] }}" class="group relative block w-full pt-[100%]">
                <div class="absolute inset-0 w-full h-full flex flex-col justify-end">
                    <div class="relative w-full h-full perspective-1000">
                        {{-- Folder Back --}}
                        <svg viewBox="0 0 174 168" fill="none" xmlns="http://www.w3.org/2000/svg" class="absolute inset-0 w-full h-full object-contain object-bottom z-0 transition-colors duration-500 text-[#3D3D3D] group-hover:text-[#1C3F78]">
                            <path d="M158.532 0H15C6.71573 0 0 6.71573 0 15V152.464C0 160.749 6.71574 167.464 15 167.464H158.532C166.816 167.464 173.532 160.749 173.532 152.464V15C173.532 6.71573 166.816 0 158.532 0Z" fill="currentColor"/>
                        </svg>
                        
                        {{-- Papers --}}
                        <img src="{{ asset('assets/images/folders/paper 1.svg') }}" class="absolute inset-0 w-[75%] mx-auto h-full object-contain object-bottom z-10 origin-bottom transition-all duration-500 ease-out group-hover:-translate-y-8 group-hover:-translate-x-3 group-hover:-rotate-[8deg]" alt="">
                        <img src="{{ asset('assets/images/folders/paper 2.svg') }}" class="absolute inset-0 w-[75%] mx-auto h-full object-contain object-bottom z-10 origin-bottom transition-all duration-500 ease-out group-hover:-translate-y-12" alt="">
                        <img src="{{ asset('assets/images/folders/paper 3.svg') }}" class="absolute inset-0 w-[75%] mx-auto h-full object-contain object-bottom z-10 origin-bottom transition-all duration-500 ease-out group-hover:-translate-y-8 group-hover:translate-x-3 group-hover:rotate-[8deg]" alt="">
                        
                        {{-- Folder Front --}}
                        <svg viewBox="0 0 177 174" fill="none" xmlns="http://www.w3.org/2000/svg" class="absolute inset-0 w-full h-full object-contain object-bottom z-20 origin-bottom transition-all duration-500 ease-out group-hover:scale-y-[0.65] group-hover:opacity-75 text-[#3D3D3D] group-hover:text-[#27539C] drop-shadow-xl">
                            <foreignObject x="-4.24728" y="-4.24728" width="184.997" height="182.495"><div xmlns="http://www.w3.org/1999/xhtml" style="backdrop-filter:blur(2.12px);clip-path:url(#clip_{{$index}});height:100%;width:100%"></div></foreignObject><g data-figma-bg-blur-radius="4.24728">
                            <path d="M122.345 40.2407C116.48 40.2407 111.098 36.9863 108.374 31.7917L96.133 8.44903C93.4089 3.25437 88.0275 0 82.1619 0H16.3654C7.67826 0 0.625902 7.02324 0.589935 15.7103L0.000156395 158.159C-0.0360218 166.897 7.03749 174 15.7756 174H157.965C166.512 174 173.504 167.194 173.735 158.65L176.497 56.4424C176.737 47.5661 169.606 40.2407 160.727 40.2407H122.345Z" fill="currentColor"/>
                            <path d="M16.3652 0.303711H82.1621C87.9148 0.303779 93.1925 3.49519 95.8643 8.58984L108.105 31.9326C110.882 37.2272 116.367 40.5439 122.346 40.5439H160.727C169.435 40.5439 176.429 47.729 176.193 56.4346L173.432 158.643C173.205 167.022 166.347 173.696 157.965 173.696H15.7754C7.20539 173.696 0.268229 166.73 0.303711 158.16L0.893555 15.7119C0.92883 7.19196 7.84523 0.303802 16.3652 0.303711Z" stroke="url(#paint_{{$index}})" stroke-opacity="0.67" stroke-width="0.606755"/>
                            </g>
                            <defs>
                            <clipPath id="clip_{{$index}}" transform="translate(4.24728 4.24728)"><path d="M122.345 40.2407C116.48 40.2407 111.098 36.9863 108.374 31.7917L96.133 8.44903C93.4089 3.25437 88.0275 0 82.1619 0H16.3654C7.67826 0 0.625902 7.02324 0.589935 15.7103L0.000156395 158.159C-0.0360218 166.897 7.03749 174 15.7756 174H157.965C166.512 174 173.504 167.194 173.735 158.65L176.497 56.4424C176.737 47.5661 169.606 40.2407 160.727 40.2407H122.345Z"/>
                            </clipPath><linearGradient id="paint_{{$index}}" x1="178.714" y1="-2.14651e-05" x2="-36.2636" y2="83.1406" gradientUnits="userSpaceOnUse">
                            <stop stop-color="#D3D3D3"/>
                            <stop offset="0.278846" stop-color="#909090"/>
                            <stop offset="0.639423" stop-color="#D3D3D3"/>
                            <stop offset="1" stop-color="#909090"/>
                            </linearGradient>
                            </defs>
                        </svg>
                        
                        {{-- Text Overlay --}}
                        <div class="absolute inset-x-0 bottom-[12%] px-4 z-30 transition-transform duration-500 ease-out group-hover:scale-y-[0.65] origin-bottom group-hover:-translate-y-2 pointer-events-none">
                            <span class="block text-white font-display font-semibold text-sm md:text-[15px] leading-tight">{{ $doc['name'] }}</span>
                        </div>
                    </div>
                </div>
            </a>
            @endforeach
        </div>
    </div>
</section>
