<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto px-4">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                <div class="bg-white shadow rounded-lg p-4">
                    <div class="text-sm text-gray-500">Total Oportunidades</div>
                    <div class="text-2xl font-bold">{{ number_format($totalDeals ?? 0) }}</div>
                </div>

                <div class="bg-white shadow rounded-lg p-4">
                    <div class="text-sm text-gray-500">Valor Total</div>
                    <div class="text-2xl font-bold">{{ number_format($totalValue ?? 0, 2) }} MXN</div>
                </div>

                <div class="bg-white shadow rounded-lg p-4">
                    <div class="text-sm text-gray-500">Etapas</div>
                    <div class="text-2xl font-bold">{{ isset($stages) ? $stages->count() : 0 }}</div>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                <div class="bg-white shadow rounded-lg p-4">
                    <h2 class="font-semibold mb-3">Oportunidades por Etapa</h2>
                    <ul class="space-y-2">
                        @foreach ($stages ?? collect() as $stage)
                            <li class="flex items-center justify-between">
                                <span class="text-gray-700">{{ $stage->name }}</span>
                                <span
                                    class="ml-2 inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">{{ $stage->deals_count ?? 0 }}</span>
                            </li>
                        @endforeach
                    </ul>
                </div>

                <div class="bg-white shadow rounded-lg p-4">
                    <h2 class="font-semibold mb-3">Oportunidades recientes</h2>
                    @if (empty($recentDeals) || $recentDeals->isEmpty())
                        <div class="text-sm text-gray-500">No hay registros recientes.</div>
                    @else
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500">Título</th>
                                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500">Compañía</th>
                                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500">Etapa</th>
                                        <th class="px-4 py-2 text-right text-xs font-medium text-gray-500">Valor</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-100">
                                    @foreach ($recentDeals as $d)
                                        <tr>
                                            <td class="px-4 py-2 text-sm text-gray-700">{{ $d->title }}</td>
                                            <td class="px-4 py-2 text-sm text-gray-700">{{ $d->company }}</td>
                                            <td class="px-4 py-2 text-sm text-gray-700">{{ optional($d->stage)->name }}
                                            </td>
                                            <td class="px-4 py-2 text-sm text-gray-700 text-right">
                                                {{ number_format($d->value, 2) }} MXN</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
