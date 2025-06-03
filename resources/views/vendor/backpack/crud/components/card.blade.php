@php
    $params = (isset($card['params'])) ? $card['params'] : [];
    $wrappers = (isset($card['wrapper']) && is_array($card['wrapper'])) ? $card['wrapper'] : [];
    $attributes = (isset($card['attributes']) && is_array($card['attributes'])) ? $card['attributes'] : [];

    $wrapper_set = '';
    if(count($wrappers) > 0){
        foreach($wrappers as $key => $wrap){
            $wrapper_set .= $key. '="'.$wrap.'" ';
        }
    }else{
        $wrapper_set = "class='col-md-12'";
    }

    $attribute_set = '';

    if(!array_key_exists('id', $attributes)){
        $attributes['id'] = $card['name'];
    }

    if(count($attributes) > 0){
        foreach($attributes as $key => $wrap){
            $attribute_set .= $key. '="'.$wrap.'" ';
        }
    }

@endphp
<div {!! $wrapper_set !!}>
    <div class="card mt-2" {!! $attribute_set !!}>
        @if (isset($card['title']))
            <div class="card-header">
                <h5>{{ $card['title'] }}</h5>
            </div>
        @endif
        <div class="card-body p-4">
            @if(!empty($card['buttons']['header']['left']) || !empty($card['buttons']['header']['right']))
                <div class="d-flex justify-content-between mb-3">
                    <div class="left-buttons d-flex gap-2">
                        @foreach($card['buttons']['header']['left'] ?? [] as $btn)
                            <button type="button" class="{{ $btn['class'] ?? 'btn btn-secondary' }}"
                                    onclick="{{ $btn['action'] ?? '' }}">
                                @if (!empty($btn['icon']))
                                    <i class="{{ $btn['icon'] }}"></i>
                                @endif
                                {{ $btn['label'] ?? 'Button' }}
                            </button>
                        @endforeach
                    </div>
                    <div class="right-buttons d-flex gap-2">
                        @foreach($card['buttons']['header']['right'] ?? [] as $btn)
                            <button type="button" class="{{ $btn['class'] ?? 'btn btn-secondary' }}"
                                    onclick="{{ $btn['action'] ?? '' }}">
                                @if (!empty($btn['icon']))
                                    <i class="{{ $btn['icon'] }}"></i>
                                @endif
                                {{ $btn['label'] ?? 'Button' }}
                            </button>
                        @endforeach
                    </div>
                </div>
            @endif
            @include($card['view'], $params)
        </div>
        @if(!empty($card['buttons']['footer']['left']) || !empty($card['buttons']['footer']['right']))
            <div class="card-footer d-flex justify-content-between">
                <div class="left-buttons d-flex gap-2">
                    @foreach($card['buttons']['footer']['left'] ?? [] as $btn)
                        <button type="button" class="{{ $btn['class'] ?? 'btn btn-secondary' }}"
                                onclick="{{ $btn['action'] ?? '' }}">
                            @if (!empty($btn['icon']))
                                <i class="{{ $btn['icon'] }}"></i>
                            @endif
                            {{ $btn['label'] ?? 'Button' }}
                        </button>
                    @endforeach
                </div>

                <div class="right-buttons d-flex gap-2">
                    @foreach($card['buttons']['footer']['right'] ?? [] as $btn)
                        <button type="button" class="{{ $btn['class'] ?? 'btn btn-secondary' }}"
                                onclick="{{ $btn['action'] ?? '' }}">
                            @if (!empty($btn['icon']))
                                <i class="{{ $btn['icon'] }}"></i>
                            @endif
                            {{ $btn['label'] ?? 'Button' }}
                        </button>
                    @endforeach
                </div>
            </div>
        @endif
    </div>
</div>

