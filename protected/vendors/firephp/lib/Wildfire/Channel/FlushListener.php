<?php

interface Wildfire_Channel_FlushListener
{
    public function channelFlushed(Wildfire_Channel $channel);
}
