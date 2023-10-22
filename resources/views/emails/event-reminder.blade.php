Evénement : {{ $event->name }}<br>
Date : {{ $event->starts_at->isoFormat('LLL') }}<br>
Lieu : {{ $event->location }}<br>
Lien : <a href="{{ $event->url }}">{{ $event->url }}</a><br>
<br>
Message à copier pour les entraîneurs<br>
**********<br>
<x-event-text-trainers-message :event="$event"/>
**********<br>
<br>
Message à copier pour les athlètes<br>
**********<br>
<x-event-text-athletes-message :event="$event"/>
**********<br>
