@foreach($components as $component)
    @if($component instanceof \CrudBooster\Forms\Section || $component->isVisible())
        {!! $component->render() !!}
    @endif
@endforeach
