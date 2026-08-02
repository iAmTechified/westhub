@props([
    'index' => null,
    'question',
    'answer' => null,
    'content' => null,
    'appearance' => [],
    'exclusive' => false,
    'itemKey' => null,
    'showIndex' => true,
    'showDivider' => true,
])

@php
    $isExclusive = filter_var($exclusive, FILTER_VALIDATE_BOOLEAN);
    $accordionKey = json_encode((string) ($itemKey ?? $index));
    $expandedExpression = $isExclusive ? "openAccordionItem === {$accordionKey}" : 'expanded';
    $toggleExpression = $isExclusive
        ? "openAccordionItem = openAccordionItem === {$accordionKey} ? null : {$accordionKey}"
        : 'expanded = !expanded';
    $displayIndex = filter_var($showIndex, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
    $displayIndex = $displayIndex ?? (bool) $showIndex;
    $displayDivider = filter_var($showDivider, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
    $displayDivider = $displayDivider ?? (bool) $showDivider;
    $appearance = is_array($appearance) ? $appearance : [];
    $collapsedBgClass = $appearance['collapsedBgClass'] ?? 'bg-primary-300';
    $expandedBgClass = $appearance['expandedBgClass'] ?? 'bg-primary-200';
    $wrapperClass = $appearance['wrapperClass'] ?? 'w-full transition-all duration-500 rounded-3xl overflow-hidden mb-4';
    $buttonClass = $appearance['buttonClass'] ?? 'w-full flex items-center justify-between px-8 py-6 text-left focus:outline-none group';
    $questionGroupClass = $appearance['questionGroupClass']
        ?? ('flex items-center ' . ($displayIndex && filled($index) ? 'gap-6' : 'gap-0'));
    $indexClass = $appearance['indexClass'] ?? 'text-2xl font-bold font-display transition-colors duration-500';
    $titleClass = $appearance['titleClass'] ?? 'text-white text-lg md:text-xl font-bold font-display tracking-tight';
    $iconShellClass = $appearance['iconShellClass'] ?? 'relative w-10 h-10 flex-shrink-0';
    $iconLayerClass = $appearance['iconLayerClass'] ?? 'absolute inset-0 flex items-center justify-center transition-all duration-500 rounded-full bg-black/20';
    $iconClass = $appearance['iconClass'] ?? 'w-5 h-5 text-white';
    $panelClass = $appearance['panelClass'] ?? 'px-8 pb-8';
    $dividerClass = $appearance['dividerClass'] ?? 'w-full h-px bg-white/10 mb-6';
    $contentClass = $appearance['contentClass'] ?? 'max-w-4xl space-y-4 text-white/90 text-base md:text-lg leading-relaxed';
    $listClass = $appearance['listClass'] ?? 'list-disc space-y-2 pl-6';
    $pillContainerClass = $appearance['pillContainerClass'] ?? 'flex flex-wrap gap-3';
    $pillClass = $appearance['pillClass'] ?? 'inline-flex min-h-[48px] max-w-full items-center rounded-full bg-primary-50 px-4 py-3 text-base font-medium leading-relaxed text-primary-200 md:min-h-[52px] md:px-5 md:text-lg';
    $contentBlocks = is_array($content) ? $content : [];

    if ($contentBlocks === [] && filled($answer)) {
        $contentBlocks = [
            [
                'type' => 'paragraph',
                'text' => $answer,
            ],
        ];
    }
@endphp

<div x-data="{ expanded: false, hovered: false }" 
     @mouseenter="hovered = true" 
     @mouseleave="hovered = false"
     class="{{ $wrapperClass }}"
     :class="({{ $expandedExpression }} || hovered) ? '{{ $expandedBgClass }}' : '{{ $collapsedBgClass }}'">
    
    <button @click="{{ $toggleExpression }}" 
            class="{{ $buttonClass }}">
        
        <div class="{{ $questionGroupClass }}">
            @if($displayIndex && filled($index))
                <span class="{{ $indexClass }}"
                      :class="({{ $expandedExpression }} || hovered) ? 'text-white' : 'text-[#14ABD5]'">
                    {{ $index }}
                </span>
            @endif
            <span class="{{ $titleClass }}">
                {{ $question }}
            </span>
        </div>

        <div class="{{ $iconShellClass }}">
            {{-- Plus Icon --}}
            <div class="{{ $iconLayerClass }}"
                 :class="{{ $expandedExpression }} ? 'opacity-0 rotate-90 scale-50' : 'opacity-100 rotate-0 scale-100'">
                <x-icon-plus class="{{ $iconClass }}" />
            </div>
            {{-- Minus Icon --}}
            <div class="{{ $iconLayerClass }}"
                 :class="{{ $expandedExpression }} ? 'opacity-100 rotate-0 scale-100' : 'opacity-0 -rotate-90 scale-50'">
                <x-icon-minus class="{{ $iconClass }}" />
            </div>
        </div>
    </button>

    <div x-show="{{ $expandedExpression }}" 
         x-collapse 
         x-cloak>
        <div class="{{ $panelClass }}">
            @if($displayDivider)
                <div class="{{ $dividerClass }}"></div>
            @endif

            <div class="{{ $contentClass }}">
                @foreach($contentBlocks as $block)
                    @php
                        $blockType = is_array($block) ? ($block['type'] ?? 'paragraph') : 'paragraph';
                        $blockText = is_array($block) ? ($block['text'] ?? null) : $block;
                        $blockItems = is_array($block) ? ($block['items'] ?? []) : [];
                        $blockClass = is_array($block) ? ($block['class'] ?? null) : null;
                        $blockContainerClass = is_array($block) ? ($block['container_class'] ?? null) : null;
                        $blockItemClass = is_array($block) ? ($block['item_class'] ?? null) : null;
                    @endphp

                    @if($blockType === 'list' && $blockItems !== [])
                        <ul class="{{ $blockClass ?? $listClass }}">
                            @foreach($blockItems as $item)
                                <li>{{ $item }}</li>
                            @endforeach
                        </ul>
                    @elseif($blockType === 'pills' && $blockItems !== [])
                        <div class="{{ $blockContainerClass ?? $pillContainerClass }}">
                            @foreach($blockItems as $item)
                                <span class="{{ $blockItemClass ?? $pillClass }}">{{ $item }}</span>
                            @endforeach
                        </div>
                    @elseif(filled($blockText))
                        <p class="{{ $blockClass }}">{{ $blockText }}</p>
                    @endif
                @endforeach
            </div>
        </div>
    </div>
</div>
