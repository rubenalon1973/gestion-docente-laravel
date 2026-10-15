@csrf

<div class="form-grid">
    <div class="form-group">
        <label class="form-label" for="category">Categoría</label>
        <select class="form-control" id="category" name="category" required>
        <option value="software" @selected(old('category', $teacherTool?->category) === 'software')>Programa</option>
        <option value="ai" @selected(old('category', $teacherTool?->category) === 'ai')>Inteligencia artificial</option>
        </select>
    </div>
    <div class="form-group">
        <label class="form-label" for="tool_name">Nombre del programa o herramienta</label>
        <input class="form-control" id="tool_name" name="tool_name" type="text" maxlength="150" required
            value="{{ old('tool_name', $teacherTool?->tool_name) }}">
    </div>
    <div class="form-group">
        <label class="form-label" for="level">Nivel</label>
        <select class="form-control" id="level" name="level" required>
            @foreach (['básico', 'intermedio', 'avanzado'] as $level)
                <option value="{{ $level }}" @selected(old('level', $teacherTool?->level) === $level)>
                    {{ ucfirst($level) }}
                </option>
            @endforeach
        </select>
    </div>
    <div class="form-actions">
        <x-ui.button type="submit">{{ $submitLabel }}</x-ui.button>
    </div>
</div>
