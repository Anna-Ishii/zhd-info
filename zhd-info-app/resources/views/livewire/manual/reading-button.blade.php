<div class="main__supplement__btnInner" wire:click='reading'>
    <p class="txtCenter">見た！<br class="spBlock">ボタン</p>
    <!-- フラグが1ならisActiveを付ける -->
    <button class="btnWatched {{ $read_flg[0] == true ? '' : 'isActive' }}"></button>
    <p class="txtBlue txtBold txtCenter">{{ $read_flg_count }}</p>
</div>