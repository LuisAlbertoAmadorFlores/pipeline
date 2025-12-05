@extends('layouts.app')

@section('content')
    <div class="max-w-7xl mx-auto px-4">
        <div class="flex items-center justify-between py-6">
            <h1 class="text-2xl font-semibold">Pipeline Manager</h1>

            <form method="GET" class="flex items-center space-x-2">
                <input name="q" class="border rounded px-3 py-2 shadow-sm w-64"
                    placeholder="Buscar título, compañía o contacto" value="{{ $q ?? '' }}">
                <button class="bg-blue-600 text-white px-3 py-2 rounded">Buscar</button>
                @if (isset($q) && $q)
                    <a href="{{ route('deals.index') }}" class="text-sm text-gray-600 underline">Limpiar</a>
                @endif
            </form>
        </div>

        @if (session('success'))
            <div class="mb-4 bg-green-100 border border-green-200 text-green-800 px-4 py-2 rounded">{{ session('success') }}
            </div>
        @endif

        <div id="toast-area" class="fixed top-4 right-4 z-50 space-y-2"></div>

        <div id="pipeline" class="grid grid-cols-6 gap-4">
            @foreach ($stages as $stage)
                <div class="bg-gray-50 rounded shadow-lg p-2">
                    <div class="font-medium mb-2">{{ $stage->name }}</div>
                    <div class="space-y-2 dropzone min-h-[120px]" data-stage-id="{{ $stage->id }}">
                        @php
                            // If search view, groupedDeals is provided; otherwise use per-stage paginator
                            $items =
                                $groupedDeals[$stage->id] ??
                                ($paginators[$stage->id]->items() ?? ($stage->deals ?? []));
                            $paginator = $paginators[$stage->id] ?? null;
                        @endphp
                        @foreach ($items as $i => $deal)
                            @php $pos = $deal->position ?? ($paginator ? ($paginator->firstItem() + $i - 1) : ($deal->position ?? 0)); @endphp
                            <div class="bg-white rounded p-3 shadow-sm deal-item" draggable="true"
                                data-deal-id="{{ $deal->id }}" data-position="{{ $pos }}">
                                <div class="font-semibold">{{ $deal->title }}</div>
                                <div class="text-sm text-gray-600">{{ $deal->company }}</div>
                                <div class="text-sm mt-1">{{ number_format($deal->value, 2) }} MXN</div>
                                <div class="mt-2">
                                    @if (auth()->check() && auth()->user()->is_admin)
                                        <a href="{{ route('deals.edit', $deal) }}"
                                            class="inline-block bg-blue-600 text-white text-xs px-2 py-1 rounded"><i class="fa-solid fa-pencil me-1"></i> Editar</a>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>

                    @if (!isset($q) && isset($paginators[$stage->id]))
                        <div class="mt-2">
                            {{ $paginators[$stage->id]->appends(request()->except('page_stage_' . $stage->id))->links() }}
                        </div>
                    @endif
                </div>
            @endforeach
        </div>

        @if (auth()->check() && auth()->user()->is_admin)
            <div class="mt-6">
                <a href="{{ route('deals.create') }}" class="inline-block bg-green-600 text-white px-4 py-2 rounded">Crear
                    Oportunidad</a>
            </div>
        @endif
    </div>

    <script>
        (function() {
            let dragged = null;

            function onDragStart(e) {
                const el = e.target.closest('.deal-item') || e.target;
                dragged = el;
                e.dataTransfer.effectAllowed = 'move';
                e.dataTransfer.setData('text/plain', el.dataset.dealId);
                el.classList.add('ring-2', 'ring-indigo-200');
            }

            function onDragEnd(e) {
                const el = (e.target && e.target.closest) ? e.target.closest('.deal-item') : e.target;
                if (el) el.classList.remove('ring-2', 'ring-indigo-200');
                dragged = null;
            }

            function onDragOver(e) {
                e.preventDefault();
                e.dataTransfer.dropEffect = 'move';
            }

            async function onDrop(e) {
                e.preventDefault();
                const stageId = e.currentTarget.dataset.stageId;
                const dealId = e.dataTransfer.getData('text/plain');
                if (!dealId || !stageId) return;

                // compute new position: index within dropzone
                const item = document.querySelector('[data-deal-id="' + dealId + '"]');
                const children = Array.from(e.currentTarget.querySelectorAll('.deal-item'));
                // if item is already child, it will be among children; when moving it may be removed, so compute target index
                let newIndex = children.length;
                // determine drop position by Y coordinate
                const rect = e.currentTarget.getBoundingClientRect();
                const offsetY = e.clientY - rect.top;
                let acc = 0;
                for (let i = 0; i < children.length; i++) {
                    const ch = children[i];
                    const h = ch.getBoundingClientRect().height;
                    if (offsetY < acc + h / 2) {
                        newIndex = i;
                        break;
                    }
                    acc += h;
                }

                // insert in DOM visually
                if (item) {
                    if (newIndex >= children.length) {
                        e.currentTarget.appendChild(item);
                    } else {
                        e.currentTarget.insertBefore(item, children[newIndex]);
                    }
                    // animate (tailwind-friendly)
                    item.classList.add('transform', '-translate-y-1');
                    setTimeout(() => {
                        item.classList.remove('transform', '-translate-y-1');
                    }, 150);
                }

                try {
                    const resp = await fetch(`/crm/${dealId}/move`, {
                        method: 'PATCH',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute(
                                'content')
                        },
                        body: JSON.stringify({
                            stage_id: stageId,
                            position: newIndex
                        })
                    });
                    const json = await resp.json();
                    if (json.success) {
                        showToast('Oportunidad movida', 'success');
                        // update data-position attributes for items in this zone
                        const updated = Array.from(e.currentTarget.querySelectorAll('.deal-item'));
                        updated.forEach((el, idx) => el.dataset.position = idx);
                    } else {
                        console.error('Move failed', json);
                        showToast('No se pudo mover la oportunidad', 'error');
                    }
                } catch (err) {
                    console.error(err);
                    showToast('Error al mover la oportunidad', 'error');
                }
            }

            function showToast(message, type = 'success') {
                const id = 'toast-' + Date.now();
                const colors = {
                    success: 'bg-green-500',
                    error: 'bg-red-500',
                    info: 'bg-blue-500'
                };
                const toast = document.createElement('div');
                toast.id = id;
                toast.className = `${colors[type] || colors.info} text-white px-4 py-2 rounded shadow-lg`;
                toast.style.opacity = 0;
                toast.style.transition = 'opacity 0.2s, transform 0.2s';
                toast.innerText = message;
                const container = document.getElementById('toast-area');
                container.appendChild(toast);
                requestAnimationFrame(() => {
                    toast.style.opacity = 1;
                    toast.style.transform = 'translateY(0)';
                });
                setTimeout(() => {
                    toast.style.opacity = 0;
                    toast.style.transform = 'translateY(-8px)';
                    setTimeout(() => toast.remove(), 250);
                }, 1800);
            }

            document.addEventListener('DOMContentLoaded', function() {
                document.querySelectorAll('.deal-item').forEach(function(el) {
                    el.addEventListener('dragstart', onDragStart);
                    el.addEventListener('dragend', onDragEnd);
                });

                document.querySelectorAll('.dropzone').forEach(function(zone) {
                    zone.addEventListener('dragover', onDragOver);
                    zone.addEventListener('drop', onDrop);
                });
            });
        })();
    </script>
@endsection
