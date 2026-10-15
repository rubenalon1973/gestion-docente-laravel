@csrf

<div class="space-y-6">
    <fieldset class="form-section">
        <legend class="form-section-legend">Datos personales</legend>
        <p class="form-section-description">Identificación y datos de nacimiento del profesor.</p>
        <div class="form-grid">
            <div class="form-group">
                <label class="form-label" for="first_name">Nombre</label>
                <input class="form-control" id="first_name" name="first_name" type="text" maxlength="100" required
                    value="{{ old('first_name', $teacher?->first_name) }}">
            </div>
            <div class="form-group">
                <label class="form-label" for="last_names">Apellidos</label>
                <input class="form-control" id="last_names" name="last_names" type="text" maxlength="150" required
                    value="{{ old('last_names', $teacher?->last_names) }}">
            </div>
            <div class="form-group">
                <label class="form-label" for="identity_document">DNI/NIE</label>
                <input class="form-control uppercase" id="identity_document" name="identity_document" type="text" maxlength="20"
                    value="{{ old('identity_document', $teacher?->identity_document) }}">
                <p class="text-xs text-slate-500">Opcional. Se guardará sin espacios y en mayúsculas.</p>
            </div>
            <div class="form-group">
                <label class="form-label" for="birth_date">Fecha de nacimiento</label>
                <input class="form-control" id="birth_date" name="birth_date" type="date"
                    value="{{ old('birth_date', $teacher?->birth_date?->format('Y-m-d')) }}">
            </div>
            <div class="form-group">
                <label class="form-label" for="birth_place">Lugar de nacimiento</label>
                <input class="form-control" id="birth_place" name="birth_place" type="text" maxlength="150"
                    value="{{ old('birth_place', $teacher?->birth_place) }}">
            </div>
        </div>
    </fieldset>

    <fieldset class="form-section">
        <legend class="form-section-legend">Datos de contacto</legend>
        <p class="form-section-description">Información para contactar y localizar al profesor.</p>
        <div class="form-grid">
            <div class="form-group">
                <label class="form-label" for="email">Correo electrónico</label>
                <input class="form-control" id="email" name="email" type="email" maxlength="255"
                    value="{{ old('email', $teacher?->email) }}">
            </div>
            <div class="form-group">
                <label class="form-label" for="phone">Teléfono</label>
                <input class="form-control" id="phone" name="phone" type="text" maxlength="30"
                    value="{{ old('phone', $teacher?->phone) }}">
            </div>
            <div class="form-group-wide">
                <label class="form-label" for="address">Dirección</label>
                <textarea class="form-control" id="address" name="address">{{ old('address', $teacher?->address) }}</textarea>
            </div>
            <div class="form-group">
                <label class="form-label" for="locality">Localidad</label>
                <input class="form-control" id="locality" name="locality" type="text" maxlength="150"
                    value="{{ old('locality', $teacher?->locality) }}">
            </div>
            <div class="form-group">
                <label class="form-label" for="postal_code">Código postal</label>
                <input class="form-control" id="postal_code" name="postal_code" type="text" maxlength="32"
                    value="{{ old('postal_code', $teacher?->postal_code) }}">
            </div>
            <div class="form-group">
                <label class="form-label" for="province">Provincia</label>
                <input class="form-control" id="province" name="province" type="text" maxlength="150"
                    value="{{ old('province', $teacher?->province) }}">
            </div>
            <div class="form-group">
                <label class="form-label" for="country">País</label>
                <input class="form-control" id="country" name="country" type="text" maxlength="150"
                    value="{{ old('country', $teacher?->country) }}">
            </div>
        </div>
    </fieldset>

    <fieldset class="form-section">
        <legend class="form-section-legend">Perfil profesional</legend>
        <p class="form-section-description">Experiencia y capacidad para crear o actualizar contenidos.</p>
        <div class="form-grid">
            <div class="form-group-wide sm:max-w-sm">
                <label class="form-label" for="years_of_experience">Años de experiencia</label>
                <input class="form-control" id="years_of_experience" name="years_of_experience" type="number" min="0" max="100"
                    value="{{ old('years_of_experience', $teacher?->years_of_experience) }}">
            </div>
            <label class="checkbox-label">
                <input class="checkbox-control" name="creates_content" type="checkbox" value="1"
                    @checked(old('creates_content', $teacher?->creates_content ?? false))>
                Crea contenido
            </label>
            <label class="checkbox-label">
                <input class="checkbox-control" name="updates_content" type="checkbox" value="1"
                    @checked(old('updates_content', $teacher?->updates_content ?? false))>
                Actualiza contenido
            </label>
        </div>
    </fieldset>

    <fieldset class="form-section">
        <legend class="form-section-legend">Gestión interna</legend>
        <p class="form-section-description">Información de uso interno protegida por el acceso a la aplicación.</p>
        <div class="form-grid">
            <div class="form-group-wide">
                <label class="form-label" for="internal_notes">Observaciones internas</label>
                <textarea class="form-control" id="internal_notes" name="internal_notes" maxlength="5000">{{ old('internal_notes', $teacher?->internal_notes) }}</textarea>
                <p class="text-xs text-slate-500">Máximo 5.000 caracteres.</p>
            </div>
        </div>
    </fieldset>

    <div class="form-actions">
        <x-ui.button type="submit">{{ $submitLabel }}</x-ui.button>
    </div>
</div>
