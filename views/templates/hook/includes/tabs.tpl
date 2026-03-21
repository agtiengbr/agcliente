<ul class="nav nav-tabs" role="tablist">
    {foreach from=$tabs item=tab}
        <li {if $tab->getActive()} class="active" {/if}>
            <a data-toggle="tab" href="#{$tab->getId()}">
                {if $tab->getIcon()}
                    <i class="icon-{$tab->getIcon()}"></i>
                {/if}
                {$tab->getTitle()}
            </a>
        </li>
     {/foreach}
</ul>

<div class="tab-content">
    {foreach from=$tabs item=tab}
        <div class="tab-pane {if $tab->getActive()} active{/if}" id="{$tab->getId()}">
            <div class="panel">
                {$tab->getBody()}
            </div>
        </div>
    {/foreach}
</div>