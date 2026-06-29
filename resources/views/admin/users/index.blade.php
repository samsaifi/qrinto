@extends('layouts.admin')
@section('title', 'Users')

@section('content')
<div class="mb-8">
    <h1 class="font-display font-bold text-2xl text-surface-900">Users</h1>
    <p class="text-sm text-surface-500">Manage customer accounts</p>
</div>

<div class="bg-white rounded-2xl border border-surface-100 shadow-card overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full">
            <thead>
                <tr class="bg-surface-50">
                    <th class="px-6 py-3 text-left text-xs font-semibold text-surface-500 uppercase">User</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-surface-500 uppercase">Email</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-surface-500 uppercase">Role</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-surface-500 uppercase">Orders</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-surface-500 uppercase">Status</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-surface-500 uppercase">Joined</th>
                    <th class="px-6 py-3 text-right text-xs font-semibold text-surface-500 uppercase">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-surface-100">
                @forelse($users as $user)
                <tr class="hover:bg-surface-50 transition">
                    <td class="px-6 py-4">
                        <div class="flex items-center gap-3">
                            <div class="w-9 h-9 rounded-lg bg-gradient-to-br from-brand-400 to-brand-600 flex items-center justify-center flex-shrink-0">
                                <span class="text-white text-sm font-bold">{{ substr($user->name, 0, 1) }}</span>
                            </div>
                            <span class="font-semibold text-surface-800">{{ $user->name }}</span>
                        </div>
                    </td>
                    <td class="px-6 py-4 text-sm text-surface-600">{{ $user->email }}</td>
                    <td class="px-6 py-4">
                        <span class="px-2 py-0.5 text-xs font-bold rounded {{ $user->role === 'admin' ? 'bg-purple-100 text-purple-700' : 'bg-surface-100 text-surface-600' }}">
                            {{ ucfirst($user->role) }}
                        </span>
                    </td>
                    <td class="px-6 py-4 text-sm text-surface-600">{{ $user->orders_count ?? $user->orders->count() }}</td>
                    <td class="px-6 py-4">
                        <span class="px-2 py-0.5 text-xs font-bold rounded {{ $user->is_active ? 'bg-accent-100 text-accent-700' : 'bg-red-100 text-red-700' }}">
                            {{ $user->is_active ? 'Active' : 'Inactive' }}
                        </span>
                    </td>
                    <td class="px-6 py-4 text-sm text-surface-500">{{ $user->created_at->format('M d, Y') }}</td>
                    <td class="px-6 py-4 text-right">
                        <div class="flex items-center justify-end gap-2">
                            <a href="{{ route('admin.users.show', $user) }}" class="text-sm text-brand-600 hover:text-brand-700 font-medium">View</a>
                            <form action="{{ route('admin.users.toggle', $user) }}" method="POST">
                                @csrf @method('PATCH')
                                <button type="submit" class="text-sm font-medium {{ $user->is_active ? 'text-red-500 hover:text-red-700' : 'text-accent-600 hover:text-accent-700' }}">
                                    {{ $user->is_active ? 'Deactivate' : 'Activate' }}
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="7" class="px-6 py-12 text-center text-surface-400">No users found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($users->hasPages())
    <div class="px-6 py-4 border-t border-surface-100">{{ $users->links() }}</div>
    @endif
</div>
@endsection
