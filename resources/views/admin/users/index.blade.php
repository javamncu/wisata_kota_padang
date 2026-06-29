<x-admin-layout title="User" heading="Kelola User">
    <form method="GET" class="mb-4">
        <input type="search" name="q" value="{{ request('q') }}" placeholder="Cari nama / email..."
               class="w-full max-w-sm rounded-xl border-gray-200 py-2 text-sm focus:border-emerald-500 focus:ring-emerald-500">
    </form>

    <div class="overflow-x-auto rounded-2xl border border-gray-100 bg-white shadow-sm">
        <table class="min-w-full divide-y divide-gray-100 text-sm">
            <thead class="bg-gray-50 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">
                <tr>
                    <th class="px-4 py-3">Nama</th>
                    <th class="px-4 py-3">Email</th>
                    <th class="px-4 py-3">Role</th>
                    <th class="px-4 py-3">Aktivitas</th>
                    <th class="px-4 py-3">Status</th>
                    <th class="px-4 py-3 text-right">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @foreach ($users as $user)
                    @php $self = $user->is(auth()->user()); @endphp
                    <tr>
                        <td class="px-4 py-3 font-medium text-gray-900">{{ $user->name }} @if ($self)<span class="text-xs text-gray-400">(Anda)</span>@endif</td>
                        <td class="px-4 py-3 text-gray-500">{{ $user->email }}</td>
                        <td class="px-4 py-3">
                            <form method="POST" action="{{ route('admin.users.role', $user) }}" class="flex items-center gap-2">
                                @csrf @method('PATCH')
                                <select name="role" onchange="this.form.submit()" @disabled($self)
                                        class="rounded-lg border-gray-200 py-1 text-xs focus:border-emerald-500 focus:ring-emerald-500 disabled:bg-gray-50 disabled:text-gray-400">
                                    @foreach ($roles as $value => $label)
                                        <option value="{{ $value }}" @selected($user->role->value === $value)>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </form>
                        </td>
                        <td class="px-4 py-3 text-gray-500">{{ $user->reviews_count }} review · {{ $user->favorites_count }} favorit</td>
                        <td class="px-4 py-3">
                            @if ($user->is_active)
                                <span class="rounded-full bg-emerald-50 px-2.5 py-0.5 text-xs font-medium text-emerald-700">Aktif</span>
                            @else
                                <span class="rounded-full bg-red-50 px-2.5 py-0.5 text-xs font-medium text-red-600">Diblokir</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-right">
                            @unless ($self)
                                <form method="POST" action="{{ route('admin.users.toggle-active', $user) }}" class="inline">
                                    @csrf @method('PATCH')
                                    <button class="rounded-lg border px-3 py-1.5 text-xs font-medium {{ $user->is_active ? 'border-red-200 text-red-600 hover:bg-red-50' : 'border-emerald-200 text-emerald-700 hover:bg-emerald-50' }}">
                                        {{ $user->is_active ? 'Blokir' : 'Aktifkan' }}
                                    </button>
                                </form>
                            @else
                                <span class="text-xs text-gray-300">—</span>
                            @endunless
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="mt-4">{{ $users->links() }}</div>
</x-admin-layout>
