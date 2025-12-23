@php
    $params = (isset($modal['params'])) ? $modal['params'] : [];
    $modalSize = (isset($modal['size'])) ? $modal['size'] : '';
    $attributes = (isset($modal['attributes']) && is_array($modal['attributes'])) ? $modal['attributes'] : [];
    $title = (isset($modal['title'])) ? $modal['title'] : '';

    $attribute_set = '';
    if(!array_key_exists('tabindex', $attributes)){
            $attributes['tabindex'] = "-1";
    }
    if(!array_key_exists('class', $attributes)){
        $attributes['class'] = "modal fade";
    }
    if(!array_key_exists('id', $attributes)){
        $attributes['id'] = $modal['name'];
    }
    if(count($attributes) > 0){
        foreach($attributes as $key => $wrap){
            $attribute_set .= $key. '="'.$wrap.'" ';
        }
    }
@endphp

<div class="modal fade" {!! $attribute_set !!} role="dialog" aria-hidden="true">
    <div class="modal-dialog {!! $modalSize !!}" role="document">
        <div class="modal-content">
            <!-- Modal Header -->
            <div class="modal-header justify-content-center">
                <h5 class="modal-title text-{{ $modal['title_alignment'] }} w-100" id="exampleModalLabel">{{ $title }}</h5>
                <button type="button" class="btn-close position-absolute end-0 me-3" data-bs-dismiss="modal" aria-label="Close" onclick="$('#{!! $attributes['id'] !!}').modal('hide')"></button>
            </div>
            <!-- Modal Body -->
            <div class="modal-body">
                @if(!empty($modal['buttons']['header']['left']) || !empty($modal['buttons']['header']['right']))
                    <div class="d-flex justify-content-between mb-3">
                        <div class="left-buttons d-flex gap-2">
                            @foreach($modal['buttons']['header']['left'] ?? [] as $btn)
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
                            @foreach($modal['buttons']['header']['right'] ?? [] as $btn)
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
                @include($modal['view'], $params)
            </div>
            @if(!empty($modal['buttons']['footer']['left']) || !empty($modal['buttons']['footer']['right']))
                <div class="modal-footer d-flex justify-content-between">
                    <div class="left-buttons d-flex gap-2">
                        @foreach($modal['buttons']['footer']['left'] ?? [] as $btn)
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
                        @foreach($modal['buttons']['footer']['right'] ?? [] as $btn)
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
            <!-- Modal Footer -->
            {{-- <div class="modal-footer">
                <button type="button" class="btn btn-sm btn-outline-danger" data-bs-dismiss="modal">Close</button>
            </div> --}}
        </div>
    </div>
</div>
