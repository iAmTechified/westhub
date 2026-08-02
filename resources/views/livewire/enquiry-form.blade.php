<div class="bg-white/50 backdrop-blur-xl border border-white/10 rounded-[1rem] p-4 md:p-6 shadow-2xl relative overflow-hidden group">
    <form wire:submit.prevent="submit" class="relative z-10 space-y-2">
        <!-- Success Message -->
        @if (session()->has('success'))
            <div class="bg-white text-primary-300 p-4 rounded-full text-sm font-bold text-center">
                {{ session('success') }}
            </div>
        @endif

        @error('submit')
            <div class="bg-red-50 text-red-700 p-4 rounded-2xl text-sm font-bold text-center">
                {{ $message }}
            </div>
        @enderror

        <!-- Honeypot -->
        <div class="hidden">
            <input type="text" wire:model="honeypot">
        </div>

        <div class="space-y-1">
            <label for="full_name" class="block text-neutral-600 text-sm font-medium pl-1">Full Name</label>
            <input type="text" 
                   id="full_name" 
                   wire:model="full_name"
                   placeholder="Your full name"
                   class="w-full bg-transparent border border-primary-300/50 rounded-full px-5 py-4 text-neutral-600 placeholder:text-neutral-600/30 focus:outline-none focus:ring-2 focus:ring-white/20 transition-all">
            @error('full_name') <span class="text-red-300 text-xs mt-1 pl-1">{{ $message }}</span> @enderror
        </div>

        <div class="space-y-1">
            <label for="email" class="block text-neutral-600 text-sm font-medium pl-1">Email Address</label>
            <input type="email" 
                   id="email" 
                   wire:model="email"
                   placeholder="Enter your email"
                   class="w-full bg-transparent border border-primary-300/50 rounded-full px-5 py-4 text-neutral-600 placeholder:text-neutral-600/30 focus:outline-none focus:ring-2 focus:ring-white/20 transition-all">
            @error('email') <span class="text-red-300 text-xs mt-1 pl-1">{{ $message }}</span> @enderror
        </div>

        <button type="submit" 
                wire:loading.attr="disabled"
                wire:target="submit"
                class="w-full bg-neutral-50/50 text-primary-300 font-bold py-4 rounded-full shadow-lg hover:bg-white active:scale-[0.98] transition-all duration-300 text-lg disabled:opacity-70 disabled:cursor-not-allowed">
            <span wire:loading.remove wire:target="submit">Stay in touch</span>
            <span wire:loading wire:target="submit">Sending...</span>
        </button>

        <p class="text-center text-neutral-600/60 text-sm">
            By submitting, you agree to our <a href="{{ route('privacy.policy') }}" class="text-primary-300 underline hover:text-neutral-600/80 transition-all">Privacy Policy</a>
        </p>
    </form>
</div>
