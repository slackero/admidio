{if $helpTextIdLabel}
    {if is_array($helpTextIdLabel)}
        {$helpTextIdLabel = $l10n->get($helpTextIdLabel[0],$helpTextIdLabel[1])}
    {else}
        {if {is_translation_string_id string=$helpTextIdLabel}}
            {$helpTextIdLabel = $l10n->get($helpTextIdLabel)}
        {/if}
    {/if}
    <i class="fas fa-info-circle admidio-info-icon" data-toggle="popover"
    data-html="true" data-trigger="hover click" data-placement="auto"
    title="{$l10n->get('SYS_NOTE')}" data-content="{$helpTextIdLabel|escape:'html'}"></i>
{/if}
