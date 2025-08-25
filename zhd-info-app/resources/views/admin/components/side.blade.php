<div class="navbar-default sidebar" role="navigation">
        <div class="sidebar-nav navbar-collapse">
            <ul class="nav">
                @if (in_array('message', $arrow_pages, true) || in_array('manual', $arrow_pages, true))
                    <li>
                        <a href="#" class="nav-label">1.配信</a>
                        <ul class="nav nav-second-level">
                            @if (in_array('message', $arrow_pages, true))
                                <li class="message-publish @if(strpos(url()->current(),'/admin/message/')) active @endif">
                                    <a href="{{ isset($message_saved_url) && $message_saved_url->page_name == 'message-publish' ? $message_saved_url->url : '/admin/message/publish/' }}">1-1 業務連絡</a>
                                </li>
                            @endif
                            @if (in_array('manual', $arrow_pages, true))
                                <li class="manual-publish @if(strpos(url()->current(),'/admin/manual/')) active @endif ">
                                    <a href="{{ isset($manual_saved_url) && $manual_saved_url->page_name == 'manual-publish' ? $manual_saved_url->url : '/admin/manual/publish/' }}">1-2 動画マニュアル</a>
                                </li>
                            @endif
                        </ul>
                    </li>
                @endif
                @if (in_array('message-analyse', $arrow_pages, true))
                    <li>
                        <a href="#" class="nav-label">2.データ抽出</span></a>
                        <ul class="nav nav-second-level">
                            <li class="analyse-personal  @if(strpos(url()->current(),'/admin/analyse/')) active @endif">
                                <a href="{{ isset($analyse_personal_saved_url) && $analyse_personal_saved_url->page_name == 'analyse-personal' ? $analyse_personal_saved_url->url : '/admin/analyse/personal/' }}">2-1.業務連絡の閲覧状況</a>
                            </li>
                        </ul>
                    </li>
                @endif
                @if (in_array('account-shop', $arrow_pages, true) || in_array('account-admin', $arrow_pages, true) || in_array('account-mail', $arrow_pages, true) || in_array('account-admin-mail', $arrow_pages, true))
                    <li>
                        <a href="#" class="nav-label">3.管理</span></a>
                        <ul class="nav nav-second-level">
                            @if (in_array('account-shop', $arrow_pages, true))
                            
                                <li class="@if(request()->path() == 'admin/account') active @endif" ><a href="/admin/account/">3-1.店舗アカウント</a></li>
                            @endif
                            @if (in_array('account-admin', $arrow_pages, true))
                                <li class="@if(request()->path() == 'admin/account/admin' || strpos(url()->current(),'/admin/account/admin/edit')) active @endif" ><a href="/admin/account/admin">3-2.本部アカウント</a></li>
                            @endif
                            @if (in_array('account-mail', $arrow_pages, true))
                                <li class="@if(strpos(url()->current(),'/admin/account/mail')) active @endif" ><a href="/admin/account/mail">3-3.DM/BM/AMメール配信設定</a></li>
                            @endif
                            @if (in_array('account-admin-mail', $arrow_pages, true))
                                <li class="@if(strpos(url()->current(),'/admin/account/adminmail')) active @endif" ><a href="/admin/account/adminmail">3-4.本部従業員への配信設定</a></li>
                                
                                
                                
                                
                            @endif
                        </ul>
                    </li>
                @endif
                @if (in_array('ims', $arrow_pages, true))
                    <li>
                        <a href="#" class="nav-label">4.その他</span></a>
                        <ul class="nav nav-second-level">
                            <li class="{{ $is_error_ims ? 'warning' : '' }} @if(strpos(url()->current(),'/admin/manage/ims')) active @endif ">
                            
                            
                            
                            <a href="/admin/manage/ims">4-1.IMS連携</a></li>
                            
                            
                            
                            
                        </ul>
                    </li>
                @endif
                <li>
                    <a href="#" class="nav-label">Ver. {{ config('version.admin_version') }}</span></a>
                </li>
                <li>
                <ul class="nav nav-second-level"><li>
                    <a href="https://z-report-stag.zensho-i.net/admin/history" >Z-repoterへ戻る</a>
                </li></ul>
                </li>
            </ul>
        </div>
        <!-- /.sidebar-collapse -->
    </div>
    <!-- /.navbar-static-side -->