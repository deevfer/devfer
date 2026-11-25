<div style="display:grid;gap:1.25rem">
    @foreach ($previews as $locale => $mail)
        <section style="border:1px solid rgb(0 0 0 / .1);border-radius:.75rem;overflow:hidden">
            <header style="padding:.6rem 1rem;background:rgb(0 0 0 / .04);font-size:.8rem">
                <strong>{{ $locale === 'es' ? 'Español' : 'English' }}</strong> · {{ $mail['subject'] }}
            </header>
            <iframe title="{{ $locale === 'es' ? 'Vista previa en español' : 'English preview' }}" sandbox="" srcdoc="{{ view('mail.template', ['body' => $mail['body'], 'lang' => $locale])->render() }}" style="display:block;width:100%;height:720px;border:0;background:#ecece9"></iframe>
        </section>
    @endforeach
    <p style="font-size:.8rem;opacity:.7">Con datos de ejemplo. Guarda los cambios para ver la versión editada.</p>
</div>
