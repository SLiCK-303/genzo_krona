{capture name=path}<a href="/{$slack}">{$game_name}</a><span class="navigation-pipe"></span>{l s='Leaderboard' mod='genzo_krona'}{/capture}

{include file="./nav.tpl"}

<div class="krona-box">
    <h1>{l s='Leaderboard' mod='genzo_krona'}</h1>
    <div id="players">
        {foreach from=$players item=player}
            <div class="player">
                <img src="/modules/genzo_krona/views/img/avatar/{$player.avatar}">
                <h3>{$player.pseudonym}</h3>
                <div>{$points_name}: {$player.points}</div>
                <div style="clear: both;"></div>
            </div>

        {/foreach}
    </div>
    {include file="./pagination.tpl"}
</div>