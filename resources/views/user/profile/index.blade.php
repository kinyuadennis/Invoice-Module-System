@extends('layouts.user')

@section('title', 'Profile')

@section('content')
<div class="max-w-4xl" x-data="{ activeTab: 'profile' }">
    <!-- Page Header -->
    <div class="flex items-center justify-between mb-8">
        <div>
            <h1 class="text-3xl font-black text-gray-900 dark:text-white tracking-tight">Your Profile</h1>
            <p class="mt-1 text-sm font-medium text-gray-500 dark:text-[#9A9A9A]">Manage your account identity and system preferences</p>
        </div>
        <div class="flex items-center gap-3">
            <span class="flex items-center gap-2 px-3 py-1.5 bg-blue-500/5 border border-blue-500/10 rounded-full">
                <div class="w-1.5 h-1.5 rounded-full bg-blue-500 animate-pulse"></div>
                <span class="text-[10px] font-black text-blue-400 uppercase tracking-widest px-1">Session Active</span>
            </span>
        </div>
    </div>

    <!-- Success Message -->
    @if(session('success'))
    <div class="mb-6 p-4 bg-green-50 border border-green-200 rounded-lg">
        <p class="text-sm text-green-800">{{ session('success') }}</p>
    </div>
    @endif

    <!-- Tabs Navigation -->
    <div class="border-b border-gray-100 dark:border-[#1F1F1F] mb-8">
        <nav class="-mb-px flex space-x-10" aria-label="Tabs">
            <button
                @click="activeTab = 'profile'"
                :class="activeTab === 'profile' ? 'border-blue-500 text-blue-600 dark:text-blue-400' : 'border-transparent text-gray-500 dark:text-[#9A9A9A] hover:text-gray-700 dark:hover:text-white hover:border-gray-300 dark:hover:border-[#333333]'"
                class="whitespace-nowrap py-4 px-1 border-b-2 font-black text-xs uppercase tracking-widest transition-all duration-200">
                Account Identity
            </button>
            <button
                @click="activeTab = 'settings'"
                :class="activeTab === 'settings' ? 'border-blue-500 text-blue-600 dark:text-blue-400' : 'border-transparent text-gray-500 dark:text-[#9A9A9A] hover:text-gray-700 dark:hover:text-white hover:border-gray-300 dark:hover:border-[#333333]'"
                class="whitespace-nowrap py-4 px-1 border-b-2 font-black text-xs uppercase tracking-widest transition-all duration-200">
                Security & Access
            </button>
            @if($company && (auth()->user()->role === 'company_owner' || auth()->user()->role === 'admin'))
            <button
                @click="activeTab = 'company'"
                :class="activeTab === 'company' ? 'border-blue-500 text-blue-600 dark:text-blue-400' : 'border-transparent text-gray-500 dark:text-[#9A9A9A] hover:text-gray-700 dark:hover:text-white hover:border-gray-300 dark:hover:border-[#333333]'"
                class="whitespace-nowrap py-4 px-1 border-b-2 font-black text-xs uppercase tracking-widest transition-all duration-200">
                Company Entity
            </button>
            @endif
        </nav>
    </div>

    <!-- Profile Tab -->
    <div x-show="activeTab === 'profile'" x-cloak class="space-y-8 transition-all duration-300">
        <div class="bg-white dark:bg-[#1E1E1E] rounded-2xl border border-gray-100 dark:border-[#2A2A2A] p-8 shadow-sm">
            <div class="flex items-center justify-between mb-10">
                <div>
                    <h2 class="text-xl font-black text-gray-900 dark:text-white tracking-tight">Account Identity</h2>
                    <p class="text-sm font-medium text-gray-500 dark:text-[#9A9A9A]">Personal information and public presence</p>
                </div>
                <div class="p-2 bg-blue-50 dark:bg-blue-500/10 rounded-lg">
                    <svg class="w-5 h-5 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                    </svg>
                </div>
            </div>

            <form method="POST" action="{{ route('user.profile.update') }}" enctype="multipart/form-data" class="space-y-8">
                @csrf
                @method('PUT')

                <!-- Profile Photo -->
                <div class="flex items-center gap-8 p-6 bg-gray-50 dark:bg-white/[0.02] border border-gray-100 dark:border-[#2A2A2A] rounded-2xl">
                    <div class="flex-shrink-0 relative group">
                        <div class="absolute inset-0 bg-blue-500 rounded-full blur-md opacity-20 group-hover:opacity-40 transition-opacity"></div>
                        @if($user->profile_photo_path)
                        <img
                            src="{{ Storage::url($user->profile_photo_path) }}"
                            alt="{{ $user->name }}"
                            class="relative w-24 h-24 rounded-full object-cover border-4 border-white dark:border-[#1A1A1A] ring-2 ring-blue-500/20 shadow-xl">
                        @else
                        <div class="relative w-24 h-24 rounded-full bg-gradient-to-br from-blue-500 to-indigo-600 flex items-center justify-center text-white text-3xl font-black border-4 border-white dark:border-[#1A1A1A] ring-2 ring-blue-500/20 shadow-xl">
                            {{ strtoupper(substr($user->name, 0, 1)) }}
                        </div>
                        @endif
                        <div class="absolute -bottom-1 -right-1 w-8 h-8 rounded-full bg-white dark:bg-[#2A2A2A] border-2 border-gray-100 dark:border-[#1F1F1F] flex items-center justify-center shadow-lg">
                            <svg class="w-4 h-4 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>
                        </div>
                    </div>
                    <div class="flex-1">
                        <label class="block text-xs font-black text-gray-400 dark:text-[#9A9A9A] uppercase tracking-widest mb-2">Representative Media</label>
                        <input
                            type="file"
                            name="profile_photo"
                            id="profile_photo"
                            accept="image/*"
                            class="block w-full text-sm text-gray-500 cursor-pointer file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-black file:uppercase file:tracking-widest file:bg-blue-600 file:text-white hover:file:bg-blue-700 transition-all">
                        <div class="flex items-center gap-4 mt-3">
                            <p class="text-[10px] font-medium text-gray-500 dark:text-[#6B6B6B]">JPG, PNG or GIF (Max 2MB)</p>
                            @if($user->profile_photo_path)
                            <button type="button"
                                onclick="event.preventDefault(); document.getElementById('delete-photo-form').submit();"
                                class="text-[10px] font-black text-red-500 hover:text-red-600 uppercase tracking-widest transition-colors">
                                Remove Avatar
                            </button>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                    <div>
                        <label for="name" class="block text-xs font-black text-gray-400 dark:text-[#9A9A9A] uppercase tracking-widest mb-2">Legal Name</label>
                        <input type="text" name="name" id="name" value="{{ old('name', $user->name) }}" required
                            class="block w-full rounded-xl border-gray-200 dark:border-[#333333] dark:bg-black/50 dark:text-white focus:border-blue-500 focus:ring-blue-500 sm:text-sm p-3 transition-colors">
                    </div>

                    <div>
                        <label for="email" class="block text-xs font-black text-gray-400 dark:text-[#9A9A9A] uppercase tracking-widest mb-2">Email Address</label>
                        <input type="email" name="email" id="email" value="{{ old('email', $user->email) }}" required
                            class="block w-full rounded-xl border-gray-200 dark:border-[#333333] dark:bg-black/50 dark:text-white focus:border-blue-500 focus:ring-blue-500 sm:text-sm p-3 transition-colors">
                    </div>
                </div>

                <div class="p-4 bg-amber-500/5 border border-amber-500/10 rounded-2xl flex items-center justify-between">
                    <div>
                        <p class="text-[10px] font-black text-amber-500 uppercase tracking-widest">Authority Level</p>
                        <p class="text-sm font-bold text-gray-900 dark:text-[#F5F5F5]">{{ ucfirst(str_replace('_', ' ', $user->role ?? 'user')) }}</p>
                    </div>
                    <div class="badge-amber">System Assigned</div>
                </div>

                <div class="flex items-center justify-end pt-4">
                    <button type="submit" class="w-full sm:w-auto px-10 py-3 rounded-xl shadow-lg shadow-blue-500/20 font-black uppercase tracking-widest text-xs text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors">
                        Publish Changes
                    </button>
                </div>
            </form>
        </div>

        @if($user->profile_photo_path)
        <form id="delete-photo-form" method="POST" action="{{ route('user.profile.photo.delete') }}" class="hidden">
            @csrf
            @method('DELETE')
        </form>
        @endif

        <!-- Quick Links -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <a href="{{ route('user.invoices.index') }}" class="interactive-card block p-8 bg-white dark:bg-[#111111] border border-gray-100 dark:border-[#1F1F1F] rounded-2xl group">
                <div class="flex items-center gap-6">
                    <div class="icon-bg-blue p-3 rounded-xl group-hover:scale-110 transition-transform">
                        <svg class="w-6 h-6 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                    </div>
                    <div>
                        <p class="text-lg font-black text-gray-900 dark:text-white tracking-tight">Ledger Authority</p>
                        <p class="text-sm font-medium text-gray-500 dark:text-[#9A9A9A]">View and manage all active invoices</p>
                    </div>
                </div>
            </a>

            <a href="{{ route('user.payments.index') }}" class="interactive-card block p-8 bg-white dark:bg-[#111111] border border-gray-100 dark:border-[#1F1F1F] rounded-2xl group">
                <div class="flex items-center gap-6">
                    <div class="icon-bg-emerald p-3 rounded-xl group-hover:scale-110 transition-transform">
                        <svg class="w-6 h-6 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <div>
                        <p class="text-lg font-black text-gray-900 dark:text-white tracking-tight">Payment Streams</p>
                        <p class="text-sm font-medium text-gray-500 dark:text-[#9A9A9A]">Configure direct settlement methods</p>
                    </div>
                </div>
            </a>
        </div>
    </div>

    <!-- Security & Access Tab -->
    <div x-show="activeTab === 'settings'" x-cloak class="space-y-8 transition-all duration-300">
        <div class="bg-white dark:bg-[#1E1E1E] rounded-2xl border border-gray-100 dark:border-[#2A2A2A] p-8 shadow-sm">
            <div class="flex items-center justify-between mb-10">
                <div>
                    <h2 class="text-xl font-black text-gray-900 dark:text-white tracking-tight">Access Control</h2>
                    <p class="text-sm font-medium text-gray-500 dark:text-[#9A9A9A]">Credential management and security protocols</p>
                </div>
                <div class="p-2 bg-red-50 dark:bg-red-500/10 rounded-lg">
                    <svg class="w-5 h-5 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                    </svg>
                </div>
            </div>

            <form method="POST" action="{{ route('user.profile.password.update') }}" class="space-y-8">
                @csrf
                @method('PUT')

                <div class="grid grid-cols-1 gap-8">
                    <div>
                        <label for="current_password" class="block text-xs font-black text-gray-400 dark:text-[#9A9A9A] uppercase tracking-widest mb-2">Current Authority Token</label>
                        <input type="password" name="current_password" id="current_password" placeholder="••••••••" required
                            class="block w-full rounded-xl border-gray-200 dark:border-[#333333] dark:bg-black/50 dark:text-white focus:border-blue-500 focus:ring-blue-500 sm:text-sm p-3 transition-colors">
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                        <div>
                            <label for="password" class="block text-xs font-black text-gray-400 dark:text-[#9A9A9A] uppercase tracking-widest mb-2">New Authority Token</label>
                            <input type="password" name="password" id="password" placeholder="••••••••" required
                                class="block w-full rounded-xl border-gray-200 dark:border-[#333333] dark:bg-black/50 dark:text-white focus:border-blue-500 focus:ring-blue-500 sm:text-sm p-3 transition-colors">
                        </div>

                        <div>
                            <label for="password_confirmation" class="block text-xs font-black text-gray-400 dark:text-[#9A9A9A] uppercase tracking-widest mb-2">Confirm New Token</label>
                            <input type="password" name="password_confirmation" id="password_confirmation" placeholder="••••••••" required
                                class="block w-full rounded-xl border-gray-200 dark:border-[#333333] dark:bg-black/50 dark:text-white focus:border-blue-500 focus:ring-blue-500 sm:text-sm p-3 transition-colors">
                        </div>
                    </div>
                </div>

                <div class="flex items-center justify-end pt-4">
                    <button type="submit" class="w-full sm:w-auto px-10 py-3 rounded-xl shadow-lg shadow-blue-500/20 font-black uppercase tracking-widest text-xs text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors">
                        Rotate Credentials
                    </button>
                </div>
            </form>
        </div>

        <div class="bg-white dark:bg-[#1E1E1E] rounded-2xl border border-gray-100 dark:border-[#2A2A2A] p-8 shadow-sm">
            <div class="flex items-center justify-between mb-8">
                <div>
                    <h2 class="text-lg font-black text-gray-900 dark:text-white tracking-tight">Signal Configuration</h2>
                    <p class="text-sm font-medium text-gray-500 dark:text-[#9A9A9A]">Automated system alerting preferences</p>
                </div>
                <div class="p-2 bg-amber-50 dark:bg-amber-500/10 rounded-lg">
                    <svg class="w-5 h-5 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                    </svg>
                </div>
            </div>

            <div class="space-y-6">
                <div class="flex items-center justify-between p-6 bg-gray-50 dark:bg-white/[0.02] border border-gray-100 dark:border-[#2A2A2A] rounded-2xl group hover:border-blue-500/30 transition-all duration-300">
                    <div class="flex items-center gap-4">
                        <div class="w-10 h-10 rounded-xl bg-blue-500/10 flex items-center justify-center">
                            <svg class="w-5 h-5 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                            </svg>
                        </div>
                        <div>
                            <p class="text-sm font-black text-gray-900 dark:text-white uppercase tracking-widest">Email Protocols</p>
                            <p class="text-xs font-medium text-gray-500 dark:text-[#9A9A9A]">Receive critical system status updates</p>
                        </div>
                    </div>
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="checkbox" class="sr-only peer" checked>
                        <div class="w-12 h-6 bg-gray-200 dark:bg-[#2A2A2A] peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[4px] after:left-[4px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-blue-600 shadow-inner"></div>
                    </label>
                </div>

                <div class="flex items-center justify-between p-6 bg-gray-50 dark:bg-white/[0.02] border border-gray-100 dark:border-[#2A2A2A] rounded-2xl group hover:border-emerald-500/30 transition-all duration-300">
                    <div class="flex items-center gap-4">
                        <div class="w-10 h-10 rounded-xl bg-emerald-500/10 flex items-center justify-center">
                            <svg class="w-5 h-5 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <div>
                            <p class="text-sm font-black text-gray-900 dark:text-white uppercase tracking-widest">Temporal Alerts</p>
                            <p class="text-xs font-medium text-gray-500 dark:text-[#9A9A9A]">Proactive reminders for aging ledger items</p>
                        </div>
                    </div>
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="checkbox" class="sr-only peer" checked>
                        <div class="w-12 h-6 bg-gray-200 dark:bg-[#2A2A2A] peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[4px] after:left-[4px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-emerald-600 shadow-inner"></div>
                    </label>
                </div>
            </div>
        </div>
    </div>

    <!-- Company Details Tab -->
    @if($company && (auth()->user()->role === 'company_owner' || auth()->user()->role === 'admin'))
    <div x-show="activeTab === 'company'" x-cloak class="transition-all duration-300">
        <div class="bg-white dark:bg-[#1E1E1E] rounded-2xl border border-gray-100 dark:border-[#2A2A2A] p-8 shadow-sm">
            <div class="flex items-center justify-between mb-10">
                <div>
                    <h2 class="text-xl font-black text-gray-900 dark:text-white tracking-tight">Active Entity Details</h2>
                    <p class="text-sm font-medium text-gray-500 dark:text-[#9A9A9A]">Regulatory and logistical identification</p>
                </div>
                <div class="p-2 bg-purple-50 dark:bg-purple-500/10 rounded-lg">
                    <svg class="w-5 h-5 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                    </svg>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                <div class="p-6 bg-gray-50 dark:bg-white/[0.02] border border-gray-100 dark:border-[#2A2A2A] rounded-2xl group hover:border-purple-500/30 transition-all">
                    <span class="text-[10px] font-black text-gray-400 dark:text-[#9A9A9A] uppercase tracking-widest block mb-2">Legal Registered Name</span>
                    <p class="text-lg font-black text-gray-900 dark:text-white tracking-tight">{{ $company->name }}</p>
                </div>

                <div class="p-6 bg-gray-50 dark:bg-white/[0.02] border border-gray-100 dark:border-[#2A2A2A] rounded-2xl group hover:border-blue-500/30 transition-all">
                    <span class="text-[10px] font-black text-gray-400 dark:text-[#9A9A9A] uppercase tracking-widest block mb-2">Fiscal Identification (PIN)</span>
                    <p class="text-lg font-black text-gray-900 dark:text-white tracking-tight">{{ $company->kra_pin ?? 'Not Declared' }}</p>
                </div>

                <div class="p-6 bg-gray-50 dark:bg-white/[0.02] border border-gray-100 dark:border-[#2A2A2A] rounded-2xl group hover:border-amber-500/30 transition-all">
                    <span class="text-[10px] font-black text-gray-400 dark:text-[#9A9A9A] uppercase tracking-widest block mb-2">Operational Terminal Address</span>
                    <p class="text-sm font-bold text-gray-700 dark:text-gray-300 leading-relaxed">{{ $company->address ?? 'No physical address on record' }}</p>
                </div>

                <div class="p-6 bg-gray-50 dark:bg-white/[0.02] border border-gray-100 dark:border-[#2A2A2A] rounded-2xl group hover:border-emerald-500/30 transition-all">
                    <span class="text-[10px] font-black text-gray-400 dark:text-[#9A9A9A] uppercase tracking-widest block mb-2">Direct Communications Line</span>
                    <p class="text-lg font-bold text-gray-900 dark:text-[#F5F5F5]">{{ $company->phone ?? 'Unset' }}</p>
                </div>
            </div>

            <div class="mt-10 flex items-center justify-between p-6 bg-blue-500/5 border border-blue-500/10 rounded-2xl">
                <div class="flex items-center gap-4">
                    <div class="w-10 h-10 rounded-xl bg-blue-500/10 flex items-center justify-center">
                        <svg class="w-5 h-5 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                    </div>
                    <div>
                        <p class="text-sm font-black text-gray-900 dark:text-white uppercase tracking-widest">Configuration Center</p>
                        <p class="text-xs font-medium text-gray-500 dark:text-[#9A9A9A]">Deep management for brand and fiscal settings</p>
                    </div>
                </div>
                <a href="{{ route('user.companies.edit', $company->id) }}" class="inline-flex items-center px-6 py-2.5 bg-white dark:bg-white/5 border border-gray-200 dark:border-white/10 text-xs font-black text-blue-600 dark:text-blue-400 rounded-xl hover:bg-blue-50 dark:hover:bg-white/10 transition-all uppercase tracking-widest">
                    Access Entity Settings
                </a>
            </div>
        </div>
    </div>
    @endif
</div>
@endif
</div>
@endsection