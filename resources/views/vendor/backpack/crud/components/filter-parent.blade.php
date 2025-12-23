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

<div>
    @include($card['view'], $params)
</div>
