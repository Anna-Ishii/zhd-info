<!DOCTYPE html>
<html lang="ja">
    <head>
        <meta charset="UTF-8" />
        <meta http-equiv="X-UA-Compatible" content="IE=edge" />
        <meta name="viewport" content="width=device-width, initial-scale=1.0" />

        <!-- Google fonts -->
        <link rel="preconnect" href="https://fonts.googleapis.com" />
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
        <link
            href="https://fonts.googleapis.com/css2?family=Noto+Sans:ital,wght@0,100..900;1,100..900&family=Roboto:ital,wght@0,100..900;1,100..900&display=swap"
            rel="stylesheet"
        />

        <!-- css -->
        <link rel="stylesheet" href="./assets/css/common.min.css" />
        <!-- 全ページ共通のCSS -->
        <link rel="stylesheet" href="./assets/css/manual-management.min.css" />
        <!-- ページごとのCSS -->
        <script src="https://unpkg.com/draggabilly@2/dist/draggabilly.pkgd.min.js"></script>
        <script src="https://unpkg.com/packery@2/dist/packery.pkgd.min.js"></script>
        <!-- Primary Meta Tags -->
        <meta name="title" content="" />
        <meta name="description" content="" />

        <title>指示作成</title>
    </head>

    <body>
        <header class="l-header">
            <div class="l-header__top">
                <h1><a href="/top.html"><img src="./assets/img/logo.svg" alt="Z-Reporter"></a></h1>
                <div class="l-header__top__info">
                    <p>ログイン中：加藤 真人/Kato Masato</p>
                    <div class="hamburger">
                        <span></span>
                        <span></span>
                        <span></span>
                    </div>
                    <nav class="hamburger-menu" id="hamburgerMenu">
                        <button class="hamburger-menu__close" id="closeHamburgerMenu">
                            <span></span>
                            <span></span>
                        </button>
                        <ul>
                            <li><a class="hamburger__link" href="/report-list.html">報告一覧</a></li>
                            <li><a class="hamburger__link" href="/instruction-create.html">指示作成</a></li>
                            <li><a class="hamburger__link" href="/user-management.html">ユーザー管理</a></li>
                            <li><a class="hamburger__link" href="/user-management-password.html">パスワード変更</a></li>
                            <li><button class="hamburger__logout">ログアウト</button></li>
                        </ul>
                    </nav>
                    <div class="overlay" id="hamburgerOverlay"></div>
                </div>
            </div>
            <div class="l-header__bottom">
                <div class="l-header__bottom__wrap">
                    <div class="l-header__back"><a class="prev" href="#"><img src="/assets/img/back-icon.svg" alt="">戻る</a></div>
                    <p class="l-header__bottom__ttl">業態ごと設定</p>
                </div>
            </div>
        </header>


        <main>
            <div class="form">
                <div class="tabs__container">
                    <p class="tabs__item active">HSコード</p>
                    <p class="tabs__item">業態名</p>
                    <p class="tabs__item">業態名</p>
                    <p class="tabs__item">業態名</p>
                    <p class="tabs__item">業態名</p>
                    <p class="tabs__item">業態名</p>
                    <p class="tabs__item">業態名</p>
                    <p class="tabs__item">業態名</p>
                    <p class="tabs__item">業態名</p>
                </div>

                <div class="ttl-item">
                    <div class="ttl__wrap">
                        <p class="ttl">マニュアル内 カテゴリ設定</p>
                        <p class="txt">マニュアル内のカテゴリの設定を行います。カテゴリ名と小カテゴリ名を入力し、更新してください。</p>
                    </div>
                </div>

                <div class="add-category">
                    <div class="category">
                        <div class="category__header">
                            <input class="category__title" placeholder="新しいカテゴリ名を追加"></input>
                        </div>
                        <div class="category__content">
                            <div class="subcategory__list">
                                <div class="subcategory__item">
                                    <span class="drag-icon"><img class="editonly move-select-item" src="./assets/img/select-drag.svg" alt="" style="touch-action: none;"></span>
                                    <input class="subcategory__name" placeholder="小カテゴリ名を入力してください"></input>
                                    <span class="subcategory__actions">
                                        <button class="delete-btn"><img src="./assets/img/delete_icon.svg" alt="削除"></button>
                                        <button class="up-btn"><svg width="22" height="22" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" class="upbtn"><path d="M3.5 10.3333L12 2M12 2L20.5 10.3333M12 2V22" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"></path></svg></button>
                                        <button class="down-btn"><svg width="22" height="22" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" class="downbtn"><path d="M3.5 13.6667L12 22M12 22L20.5 13.6667M12 22V2" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"></path></svg></button>
                                    </span>
                                </div>
                            </div>
                            
                            <span class="category__actions">
                                <button class="delete-btn"><img src="./assets/img/delete_icon.svg" alt="削除"></button>
                                <button class="up-btn"><svg width="22" height="22" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" class="upbtn"><path d="M3.5 10.3333L12 2M12 2L20.5 10.3333M12 2V22" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"></path></svg></button>
                                <button class="down-btn"><svg width="22" height="22" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" class="downbtn"><path d="M3.5 13.6667L12 22M12 22L20.5 13.6667M12 22V2" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"></path></svg></button>
                            </span>
                        </div>
                        <button class="add-subcategory">＋小カテゴリを追加</button>
                    </div>
                </div>

                <div class="form__container">
                    <div class="form__item">
                        <div class="category">
                            <img class="category__drag" src="./assets/img/drag.svg" alt="">
                            <div class="category__header">
                                <input class="category__title" value="商品関連"></input>
                            </div>
                            <div class="category__content">
                                <div class="subcategory__list">
                                    <div class="subcategory__item">
                                        <span class="drag-icon"><img class="editonly move-select-item" src="./assets/img/select-drag.svg" alt="" style="touch-action: none;"></span>
                                        <input class="subcategory__name" value="握り"></input>
                                        <span class="subcategory__actions">
                                            <button class="delete-btn"><img src="./assets/img/delete_icon.svg" alt="削除"></button>
                                            <button class="up-btn"><svg width="22" height="22" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" class="upbtn"><path d="M3.5 10.3333L12 2M12 2L20.5 10.3333M12 2V22" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"></path></svg></button>
                                            <button class="down-btn"><svg width="22" height="22" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" class="downbtn"><path d="M3.5 13.6667L12 22M12 22L20.5 13.6667M12 22V2" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"></path></svg></button>
                                        </span>
                                    </div>
                                    <div class="subcategory__item">
                                        <span class="drag-icon"><img class="editonly move-select-item" src="./assets/img/select-drag.svg" alt="" style="touch-action: none;"></span>
                                        <input class="subcategory__name" value="軍艦"></input>
                                        <span class="subcategory__actions">
                                            <button class="delete-btn"><img src="./assets/img/delete_icon.svg" alt="削除"></button>
                                            <button class="up-btn"><svg width="22" height="22" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" class="upbtn"><path d="M3.5 10.3333L12 2M12 2L20.5 10.3333M12 2V22" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"></path></svg></button>
                                            <button class="down-btn"><svg width="22" height="22" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" class="downbtn"><path d="M3.5 13.6667L12 22M12 22L20.5 13.6667M12 22V2" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"></path></svg></button>
                                        </span>
                                    </div>
                                    <div class="subcategory__item">
                                        <span class="drag-icon"><img class="editonly move-select-item" src="./assets/img/select-drag.svg" alt="" style="touch-action: none;"></span>
                                        <input class="subcategory__name" value="デザート"></input>
                                        <span class="subcategory__actions">
                                            <button class="delete-btn"><img src="./assets/img/delete_icon.svg" alt="削除"></button>
                                            <button class="up-btn"><svg width="22" height="22" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" class="upbtn"><path d="M3.5 10.3333L12 2M12 2L20.5 10.3333M12 2V22" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"></path></svg></button>
                                            <button class="down-btn"><svg width="22" height="22" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" class="downbtn"><path d="M3.5 13.6667L12 22M12 22L20.5 13.6667M12 22V2" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"></path></svg></button>
                                        </span>
                                    </div>
                                    <div class="subcategory__item">
                                        <span class="drag-icon"><img class="editonly move-select-item" src="./assets/img/select-drag.svg" alt="" style="touch-action: none;"></span>
                                        <input class="subcategory__name" value="巻き"></input>
                                        <span class="subcategory__actions">
                                            <button class="delete-btn"><img src="./assets/img/delete_icon.svg" alt="削除"></button>
                                            <button class="up-btn"><svg width="22" height="22" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" class="upbtn"><path d="M3.5 10.3333L12 2M12 2L20.5 10.3333M12 2V22" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"></path></svg></button>
                                            <button class="down-btn"><svg width="22" height="22" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" class="downbtn"><path d="M3.5 13.6667L12 22M12 22L20.5 13.6667M12 22V2" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"></path></svg></button>
                                        </span>
                                    </div>
                                    <div class="subcategory__item">
                                        <span class="drag-icon"><img class="editonly move-select-item" src="./assets/img/select-drag.svg" alt="" style="touch-action: none;"></span>
                                        <input class="subcategory__name" value="切符"></input>
                                        <span class="subcategory__actions">
                                            <button class="delete-btn"><img src="./assets/img/delete_icon.svg" alt="削除"></button>
                                            <button class="up-btn"><svg width="22" height="22" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" class="upbtn"><path d="M3.5 10.3333L12 2M12 2L20.5 10.3333M12 2V22" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"></path></svg></button>
                                            <button class="down-btn"><svg width="22" height="22" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" class="downbtn"><path d="M3.5 13.6667L12 22M12 22L20.5 13.6667M12 22V2" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"></path></svg></button>
                                        </span>
                                    </div>
                                    <div class="subcategory__item">
                                        <span class="drag-icon"><img class="editonly move-select-item" src="./assets/img/select-drag.svg" alt="" style="touch-action: none;"></span>
                                        <input class="subcategory__name" value="汁・麺類"></input>
                                        <span class="subcategory__actions">
                                            <button class="delete-btn"><img src="./assets/img/delete_icon.svg" alt="削除"></button>
                                            <button class="up-btn"><svg width="22" height="22" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" class="upbtn"><path d="M3.5 10.3333L12 2M12 2L20.5 10.3333M12 2V22" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"></path></svg></button>
                                            <button class="down-btn"><svg width="22" height="22" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" class="downbtn"><path d="M3.5 13.6667L12 22M12 22L20.5 13.6667M12 22V2" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"></path></svg></button>
                                        </span>
                                    </div>
                                    <div class="subcategory__item">
                                        <span class="drag-icon"><img class="editonly move-select-item" src="./assets/img/select-drag.svg" alt="" style="touch-action: none;"></span>
                                        <input class="subcategory__name" value="ホット食品"></input>
                                        <span class="subcategory__actions">
                                            <button class="delete-btn"><img src="./assets/img/delete_icon.svg" alt="削除"></button>
                                            <button class="up-btn"><svg width="22" height="22" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" class="upbtn"><path d="M3.5 10.3333L12 2M12 2L20.5 10.3333M12 2V22" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"></path></svg></button>
                                            <button class="down-btn"><svg width="22" height="22" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" class="downbtn"><path d="M3.5 13.6667L12 22M12 22L20.5 13.6667M12 22V2" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"></path></svg></button>
                                        </span>
                                    </div>
                                </div>
                                
                                <span class="category__actions">
                                    <button class="delete-btn"><img src="./assets/img/delete_icon.svg" alt="削除"></button>
                                    <button class="up-btn"><svg width="22" height="22" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" class="upbtn"><path d="M3.5 10.3333L12 2M12 2L20.5 10.3333M12 2V22" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"></path></svg></button>
                                    <button class="down-btn"><svg width="22" height="22" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" class="downbtn"><path d="M3.5 13.6667L12 22M12 22L20.5 13.6667M12 22V2" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"></path></svg></button>
                                </span>
                            </div>
                            <button class="add-subcategory">＋小カテゴリを追加</button>
                        </div>
                    </div>

                    <div class="form__item">
                        <div class="category">
                            <img class="category__drag" src="./assets/img/drag.svg" alt="">
                            <div class="category__header">
                                <input class="category__title" value="フェア"></input>
                            </div>
                            <div class="category__content">
                                <div class="subcategory__list">
                                    <div class="subcategory__item">
                                        <span class="drag-icon"><img class="editonly move-select-item" src="./assets/img/select-drag.svg" alt="" style="touch-action: none;"></span>
                                        <input class="subcategory__name" value="小カテゴリ名が入ります"></input>
                                        <span class="subcategory__actions">
                                            <button class="delete-btn"><img src="./assets/img/delete_icon.svg" alt="削除"></button>
                                            <button class="up-btn"><svg width="22" height="22" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" class="upbtn"><path d="M3.5 10.3333L12 2M12 2L20.5 10.3333M12 2V22" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"></path></svg></button>
                                            <button class="down-btn"><svg width="22" height="22" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" class="downbtn"><path d="M3.5 13.6667L12 22M12 22L20.5 13.6667M12 22V2" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"></path></svg></button>
                                        </span>
                                    </div>
                                    <div class="subcategory__item">
                                        <span class="drag-icon"><img class="editonly move-select-item" src="./assets/img/select-drag.svg" alt="" style="touch-action: none;"></span>
                                        <input class="subcategory__name" value="小カテゴリ名が入ります"></input>
                                        <span class="subcategory__actions">
                                            <button class="delete-btn"><img src="./assets/img/delete_icon.svg" alt="削除"></button>
                                            <button class="up-btn"><svg width="22" height="22" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" class="upbtn"><path d="M3.5 10.3333L12 2M12 2L20.5 10.3333M12 2V22" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"></path></svg></button>
                                            <button class="down-btn"><svg width="22" height="22" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" class="downbtn"><path d="M3.5 13.6667L12 22M12 22L20.5 13.6667M12 22V2" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"></path></svg></button>
                                        </span>
                                    </div>
                                    <div class="subcategory__item">
                                        <span class="drag-icon"><img class="editonly move-select-item" src="./assets/img/select-drag.svg" alt="" style="touch-action: none;"></span>
                                        <input class="subcategory__name" value="小カテゴリ名が入ります"></input>
                                        <span class="subcategory__actions">
                                            <button class="delete-btn"><img src="./assets/img/delete_icon.svg" alt="削除"></button>
                                            <button class="up-btn"><svg width="22" height="22" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" class="upbtn"><path d="M3.5 10.3333L12 2M12 2L20.5 10.3333M12 2V22" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"></path></svg></button>
                                            <button class="down-btn"><svg width="22" height="22" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" class="downbtn"><path d="M3.5 13.6667L12 22M12 22L20.5 13.6667M12 22V2" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"></path></svg></button>
                                        </span>
                                    </div>
                                </div>
                                
                                <span class="category__actions">
                                    <button class="delete-btn"><img src="./assets/img/delete_icon.svg" alt="削除"></button>
                                    <button class="up-btn"><svg width="22" height="22" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" class="upbtn"><path d="M3.5 10.3333L12 2M12 2L20.5 10.3333M12 2V22" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"></path></svg></button>
                                    <button class="down-btn"><svg width="22" height="22" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" class="downbtn"><path d="M3.5 13.6667L12 22M12 22L20.5 13.6667M12 22V2" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"></path></svg></button>
                                </span>
                            </div>
                            <button class="add-subcategory">＋小カテゴリを追加</button>
                        </div>
                    </div>
                </div>
    
                <div class="c-btn">
                    <button class="c-btn__blue" type="submit">更新する</button>
                </div>
            </div>

        </main>

        <script src="./assets/js/hamburger.js"></script>
        <script src="./assets/js/ttlWrap.js"></script>
        <script src="./assets/js/manualManagement.js"></script>
        <script src="./assets/js/modal.js"></script>
        <script src="./assets/js/selectItem.js"></script>
        
    </body>
</html>