$(function() {
    $('select[name="brand_id"]').change(function() {
        var val = $(this).val();
        switch (val){
            case "1":
                var sns = { sns_twitter 	: 'https://twitter.com/sukiya_jp',
                			sns_twitter_id  : '@sukiya_jp',
                            sns_facebook    : '',
                            sns_facebook_id : '',
                            sns_innsta 		: '',
                            sns_innsta_id   : '',
                            sns_line 		: 'http://line.naver.jp/ti/p/%40sukiya',
                            sns_line_id     : '　すき家　'
                          }
                break;
            case "2":
                var sns = { sns_twitter 	: 'https://twitter.com/cocos_campaign',
                			sns_twitter_id  : '@cocos_campaign',
                            sns_facebook    : '',
                            sns_facebook_id : '',
                            sns_innsta 		: 'https://www.instagram.com/cocos_j/',
                            sns_innsta_id   : 'cocos_j',
                            sns_line 		: '',
                            sns_line_id     : ''
                          }
                break;
            case "3":
                var sns = { sns_twitter 	: 'https://twitter.com/takara_jima_jp',
                			sns_twitter_id  : '',
                            sns_facebook    : '',
                            sns_facebook_id : '',
                            sns_innsta 		: '',
                            sns_innsta_id   : '',
                            sns_line 		: '',
                            sns_line_id     : ''
                          }
                break;
            case "4":
                var sns = { sns_twitter 	: 'https://twitter.com/jollypasta_jp',
                			sns_twitter_id  : '@jollypasta_jp',
                            sns_facebook    : 'https://www.facebook.com/JollyPasta.official/',
                            sns_facebook_id : '@JollyPasta.official',
                            sns_innsta 		: 'https://www.instagram.com/jollypasta_jp/',
                            sns_innsta_id   : 'jollypasta_jp',
                            sns_line 		: '',
                            sns_line_id     : ''
                          }
                break;
            case "5":
                var sns = { sns_twitter 	: 'https://twitter.com/nakau_info',
                			sns_twitter_id  : '@nakau_info',
                            sns_facebook    : '',
                            sns_facebook_id : '',
                            sns_innsta 		: '',
                            sns_innsta_id   : '',
                            sns_line 		: '',
                            sns_line_id     : ''
                          }
                break;
            case "6":
                var sns = { sns_twitter 	: 'https://twitter.com/bigboyjapan_cam',
                			sns_twitter_id  : '@bigboyjapan_cam',
                            sns_facebook    : '',
                            sns_facebook_id : '',
                            sns_innsta 		: '',
                            sns_innsta_id   : '',
                            sns_line 		: '',
                            sns_line_id     : ''
                          }
                break;
            case "8":
                var sns = { sns_twitter 	: 'https://twitter.com/hanayayohei_j',
                			sns_twitter_id  : '@hanayayohei_jp',
                            sns_facebook    : '',
                            sns_facebook_id : '',
                            sns_innsta 		: '',
                            sns_innsta_id   : '',
                            sns_line 		: '',
                            sns_line_id     : ''
                          }
                break;
            case "10":
                var sns = { sns_twitter 	: '',
                			sns_twitter_id  : '',
                            sns_facebook    : '',
                            sns_facebook_id : '',
                            sns_innsta 		: 'https://www.instagram.com/eltorito.jp/',
                            sns_innsta_id   : 'eltorito.jp',
                            sns_line 		: '',
                            sns_line_id     : ''
                          }
                break;
            case "16":
                var sns = { sns_twitter 	: 'https://twitter.com/HAMAZUSHi_com',
                			sns_twitter_id  : '@HAMAZUSHi_com',
                            sns_facebook    : '',
                            sns_facebook_id : '',
                            sns_innsta 		: 'https://www.instagram.com/hamazushi_com/',
                            sns_innsta_id   : 'hamazushi_com',
                            sns_line 		: '',
                            sns_line_id     : ''
                          }
                break;
            case "17":
                var sns = { sns_twitter 	: 'https://twitter.com/gyuan_jp',
                			sns_twitter_id  : '',
                            sns_facebook    : '',
                            sns_facebook_id : '',
                            sns_innsta 		: '',
                            sns_innsta_id   : '',
                            sns_line 		: '',
                            sns_line_id     : ''
                          }
                break;
            case "18":
                var sns = { sns_twitter 	: 'https://twitter.com/ichi_ban_jp',
                			sns_twitter_id  : '@ichi_ban_jp',
                            sns_facebook    : '',
                            sns_facebook_id : '',
                            sns_innsta 		: '',
                            sns_innsta_id   : '',
                            sns_line 		: '',
                            sns_line_id     : ''
                          }
                break;
            case "19":
                var sns = { sns_twitter 	: 'https://twitter.com/moriva_coffee',
                			sns_twitter_id  : '@moriva_coffee',
                            sns_facebook    : '',
                            sns_facebook_id : '',
                            sns_innsta 		: 'https://www.instagram.com/moriva_coffee/',
                            sns_innsta_id   : 'moriva_coffee',
                            sns_line 		: '',
                            sns_line_id     : ''
                          }
                break;
            case "35":
                var sns = { sns_twitter 	: 'https://twitter.com/ichi_ban_jp',
                			sns_twitter_id  : '@ichi_ban_jp',
                            sns_facebook    : '',
                            sns_facebook_id : '',
                            sns_innsta 		: '',
                            sns_innsta_id   : '',
                            sns_line 		: '',
                            sns_line_id     : ''
                          }
                break;
            default:
                var sns = { sns_twitter 	: '',
                			sns_twitter_id  : '',
                            sns_facebook    : '',
                            sns_facebook_id : '',
                            sns_innsta 		: '',
                            sns_innsta_id   : '',
                            sns_line 		: '',
                            sns_line_id     : ''
                          }
                break;
        }
        $.each(sns, function(i, val) {
            $('input[name="'+ i + '"]').val(val);
        });
    })
});
