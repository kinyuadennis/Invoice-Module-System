    <!-- Page Header -->
    <div class="flex items-center justify-between mb-8">
        <div>
            <div class="flex items-center gap-2 mb-1">
                <a href="{{ route('admin.users.index') }}" class="text-xs font-bold text-blue-500 hover:text-blue-600 transition-colors flex items-center gap-1 group">
                    <svg class="w-3 h-3 transform group-hover:-translate-x-0.5 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M15 19l-7-7 7-7" />
                    </svg>
                    Back to Registry
                </a>
            </div>
            <h1 class="text-3xl font-black text-gray-900 dark:text-white tracking-tight">Modify Identity</h1>
            <p class="mt-1 text-sm font-medium text-gray-500 dark:text-[#9A9A9A]">Update permissions and profile details for {{ $user->name }}</p>
        </div>
        <div class="hidden sm:flex items-center gap-3">
            <span class="flex items-center gap-2 px-3 py-1.5 bg-indigo-500/5 border border-indigo-500/10 rounded-full">
                <div class="w-1.5 h-1.5 rounded-full bg-indigo-500 animate-pulse"></div>
                <span class="text-[10px] font-black text-indigo-400 uppercase tracking-widest px-1">Security Context</span>
            </span>
        </div>
    </div>

    <div class="max-w-3xl">
        <x-card class="relative overflow-hidden">
            <div class="absolute top-0 right-0 w-32 h-32 bg-blue-500/5 rounded-full blur-3xl -mr-16 -mt-16"></div>

            <form method="POST" action="{{ route('admin.users.update', $user->id) }}" class="space-y-8 relative z-10">
                @csrf
                @method('PUT')

                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                    <div class="space-y-6">
                        <h3 class="text-xs font-black text-gray-400 dark:text-[#9A9A9A] uppercase tracking-widest">Authentication Info</h3>
                        <x-input type="text" name="name" label="Legal Name" value="{{ old('name', $user->name) }}" required class="!rounded-xl" />
                        <x-input type="email" name="email" label="Email Address" value="{{ old('email', $user->email) }}" required class="!rounded-xl" />
                    </div>

                    <div class="space-y-6">
                        <h3 class="text-xs font-black text-gray-400 dark:text-[#9A9A9A] uppercase tracking-widest">Access Control</h3>
                        <x-select name="role" label="Account Role" :options="[
                            ['value' => 'user', 'label' => 'Standard User'],
                            ['value' => 'admin', 'label' => 'System Administrator'],
                            ['value' => 'staff', 'label' => 'Support Staff'],
                        ]" :value="old('role', $user->role)" required class="!rounded-xl" />

                        <div class="p-4 bg-gray-50 dark:bg-[#111111]/50 border border-gray-100 dark:border-white/5 rounded-2xl">
                            <div class="flex items-start gap-3">
                                <div class="p-2 bg-blue-500/10 rounded-lg">
                                    <svg class="w-4 h-4 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                </div>
                                <div>
                                    <p class="text-[10px] font-bold text-gray-900 dark:text-white uppercase tracking-tight">Security Note</p>
                                    <p class="text-[11px] text-gray-500 dark:text-[#9A9A9A] leading-relaxed mt-0.5">Role changes affect global permissions immediately. Ensure the user is notified if their access level decreases.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="pt-6 border-t border-gray-100 dark:border-white/5 flex items-center justify-end space-x-4">
                    <a href="{{ route('admin.users.index') }}" class="text-sm font-bold text-gray-500 hover:text-gray-700 dark:text-[#9A9A9A] dark:hover:text-white transition-colors">
                        Discard Changes
                    </a>
                    <x-button type="submit" variant="primary" class="!px-8 !py-3 !rounded-xl btn-ripple font-bold shadow-lg shadow-blue-500/20">
                        Commit Profile Updates
                    </x-button>
                </div>
            </form>
        </x-card>
    </div>
    @endsection