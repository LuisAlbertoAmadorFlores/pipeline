@extends('layouts.app')

@section('content')
    <div class="max-w-3xl mx-auto py-8 px-4">
        <div class="bg-white shadow rounded-lg p-6">
            <h2 class="text-xl font-semibold mb-4">{{ isset($deal) ? 'Editar Oportunidad' : 'Nueva Oportunidad' }}</h2>

            <form method="POST" action="{{ isset($deal) ? route('deals.update', $deal) : route('deals.store') }}">
                @csrf
                @if (isset($deal))
                    @method('PUT')
                @endif

                <div class="grid grid-cols-1 gap-4">
                    <div>
                        <label for="title" class="block text-sm font-medium text-gray-700">Título</label>
                        <input type="text" name="title" id="title"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                            value="{{ old('title', $deal->title ?? '') }}">
                    </div>

                    <div>
                        <label for="company" class="block text-sm font-medium text-gray-700">Compañía</label>
                        <input type="text" name="company" id="company"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                            value="{{ old('company', $deal->company ?? '') }}">
                    </div>

                    <div>
                        <label for="value" class="block text-sm font-medium text-gray-700">Valor</label>
                        <input type="number" name="value" id="value"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                            value="{{ old('value', $deal->value ?? '') }}">
                    </div>

                    <div>
                        <label for="stage_id" class="block text-sm font-medium text-gray-700">Etapa</label>
                        <select name="stage_id" id="stage_id"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            @foreach ($stages as $stage)
                                <option value="{{ $stage->id }}"
                                    {{ old('stage_id', $deal->stage_id ?? '') == $stage->id ? 'selected' : '' }}>
                                    {{ $stage->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label for="contact_name" class="block text-sm font-medium text-gray-700">Contacto</label>
                        <input type="text" name="contact_name" id="contact_name"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                            value="{{ old('contact_name', $deal->contact_name ?? '') }}">
                    </div>

                    <div>
                        <label for="notes" class="block text-sm font-medium text-gray-700">Notas</label>
                        <textarea name="notes" id="notes" rows="4"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">{{ old('notes', $deal->notes ?? '') }}</textarea>
                    </div>

                    <div class="flex items-center space-x-3 pt-2">
                        <button
                            class="inline-flex items-center px-4 py-2 bg-indigo-600 text-white rounded hover:bg-indigo-700">{{ isset($deal) ? 'Guardar' : 'Crear' }}</button>
                        <a href="{{ route('deals.index') }}"
                            class="inline-flex items-center px-4 py-2 bg-gray-200 text-gray-700 rounded hover:bg-gray-300">Cancelar</a>
                    </div>
                </div>
            </form>
        </div>
    </div>
@endsection
