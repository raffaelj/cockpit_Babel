<div>
    <ul class="uk-breadcrumb">
        <li class="uk-active"><span>Babel</span></li>
    </ul>
</div>


<div riot-view>

    <style>
        .lang_code_label {
            width: 2.5em;
            font-weight: bold;
            display: inline-block;
            margin-right: .2em;
        }
        .lang_code_label.wide {
            width: 10em;
        }
        .strings-box:focus-within {
            box-shadow: 0 25px 80px rgba(0,0,0,0.12),0 3px 12px rgba(0,0,0,0.05);
        }
        .strings-box legend {
            font-size: 14px;
            padding-bottom: 0;
            line-height: 28px;
        }
    </style>

    <form class="uk-form uk-container uk-container-center" onsubmit="{ submit }">

        <div class="uk-margin">
            <ul class="uk-tab uk-margin-large-bottom">
                <li class="{ tab=='modules' && 'uk-active'}"><a class="" onclick="{ toggleTab }" data-tab="modules">@lang('Modules')</a></li>
                <li class="{ tab=='unassigned' && 'uk-active'}"><a class="" onclick="{ toggleTab }" data-tab="unassigned">@lang('Unassigned strings')</a></li>
                <li class="{ tab=='strings' && 'uk-active'}"><a class="" onclick="{ toggleTab }" data-tab="strings">@lang('All strings')</a></li>
                <li class="{ tab=='meta' && 'uk-active'}"><a class="" onclick="{ toggleTab }" data-tab="meta">@lang('Meta')</a></li>
                <li class="{ tab=='other' && 'uk-active'}"><a class="" onclick="{ toggleTab }" data-tab="other">@lang('Other')</a></li>
            </ul>
        </div>

        <div class="uk-margin">
            @lang('Filters:')
            <button type="button" class="uk-button uk-button-small uk-button-{ highlightEmptyStrings ? 'success' : 'primary' }" onclick="{ toggleHighlightEmptyStrings }">@lang('Highlight empty fields')</button>
            <button type="button" class="uk-button uk-button-small uk-button-{ hideCompletedStrings ? 'success' : 'primary' }" onclick="{ toggleHideNonEmptyStrings }">@lang('Hide completed strings')</button>
            <button type="button" class="uk-button uk-button-small uk-button-{ allowDeletions ? 'success' : 'primary' }" onclick="{ toggleAllowDeletions }">@lang('Allow deletions')</button>

            <span class="uk-display-inline-block">
                @lang('Languages:')
                <span class="">
                    <button type="button" data-filter-lang="_all" onclick="{ toggleLangFilter }" class="uk-button uk-button-small" disabled="{ !filterLangs.length }">@lang('All')</button>
                    <button type="button" data-filter-lang="{ lang.code }" class="uk-button uk-button-small uk-margin-small-right { filterLangs.length && checkLangFilter(lang.code) ? 'uk-button-success' : '' }" title="{ lang.name }" each="{ lang in languages }" if="{ lang.code != 'en' }" onclick="{ toggleLangFilter }" data-uk-tooltip>
                        { lang.code }
                    </button>
                </span>
            </span>

            <div class="uk-margin">
                @lang('Modules:')
                <span class="">
                    <button type="button" data-filter-module="_all" onclick="{ toggleModuleFilter }" class="uk-button uk-button-small" disabled="{ !filterModules.length }">@lang('All')</button>
                    <button type="button" data-filter-module="{ moduleName }" onclick="{ toggleModuleFilter }" class="uk-button uk-button-small uk-margin-small-right { filterModules.length && checkModuleFilter(moduleName) ? 'uk-button-success' : '' }" each="{ moduleData,moduleName in stringsPerModule }" if="{ moduleName != 'unassigned' }">
                        { moduleName }
                    </button>
                </span>
            </div>

        </div>

        <div class="uk-margin uk-text-right">
            <button type="button" class="uk-button uk-button-large uk-button-primary" onclick="{ addString }">@lang('Add string')</button>
            <button type="button" class="uk-button uk-button-large" onclick="{ showEntryObject }">@lang('Show JSON')</button>
        </div>

        <div if="{ loading }">
        loading...
        </div>

        <div if="{ !loading }">

            <div show="{tab=='modules'}">
                <div class="uk-panel-box uk-panel-box-primary uk-panel uk-panel-card uk-margin" each="{ moduleData,moduleName in stringsPerModule }" if="{ moduleName != 'unassigned' }" show="{ checkModuleFilter(moduleName) }">

                    <h3 class="uk-panel-title">{ moduleName }</h3>
                    <span class="uk-panel-badge uk-badge uk-badge-notification">{ moduleData.strings.length }</span>

                    <div class="uk-grid uk-grid-small">
                        <div class="uk-width-large-1-2 uk-width-xlarge-1-3 uk-grid-margin" each="{ string,idx in moduleData.strings }" show="{ checkFilterHideCompleted(this) }">
                            <div class="uk-panel-box uk-panel-card strings-box">
                                <fieldset>
                                    <legend>{ string }</legend>

                                    <div class="uk-flex uk-flex-middle" each="{ lang in locales }" if="{ lang != 'en' }" show="{ checkLangFilter(lang) }">
                                        <label class="lang_code_label">{ lang }:</label>

                                        <input class="uk-width-1-1 { highlightEmptyStrings && !(knownTranslations[string] && knownTranslations[string][lang]) ? 'uk-form-danger' : '' }" type="text" bind="translations.{moduleName}.{lang}[{escaped(string)}]" onfocus="{ checkScroll }" />
                                    </div>
                                </fieldset>

                                <div class="uk-panel-box-footer" if="{ moduleData.context && moduleData.context[idx] }">
                                    <span>@lang('context:')</span>
                                    <ul class="uk-list">
                                        <li each="{ file in moduleData.context[idx] }">
                                            <code>{ file }</code>
                                        </li>
                                    </ul>
                                </div>

                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div show="{tab=='strings'}">
                <div class="uk-panel-box uk-panel-box-primary uk-panel uk-panel-card">

                    <h3 class="uk-panel-title">@lang('All strings')</h3>
                    <span class="uk-panel-badge uk-badge uk-badge-notification">{ Object.keys(knownTranslations).length }</span>
                    <div class="uk-form-row"  each="{ translations,string in knownTranslations }" if="{ string.substr(0,1) != '@' }" show="{ checkFilterHideCompleted(this) }">
                        <div class="uk-panel-box uk-panel-card strings-box">
                            <fieldset>
                                <legend>
                                    <span class="uk-margin-right">{ string }</span>
                                    <span class="uk-badge uk-badge-outline uk-float-right uk-margin-small-left" each="{ module in modules }" aria-hidden="true" if="{ stringsPerModule[module].strings.includes(string) && module != 'unassigned' }">
                                        {module}
                                    </span>
                                </legend>

                                <div class="uk-flex uk-flex-middle" each="{ lang in locales }" show="{ checkLangFilter(lang) }">
                                    <label class="lang_code_label" if="{ lang != 'en' }">{ lang }:</label>
                                    <input class="uk-width-1-1 { highlightEmptyStrings && !(knownTranslations[string] && knownTranslations[string][lang]) ? 'uk-form-danger' : '' }" type="text" bind="knownTranslations[{escaped(string)}].{lang}" if="{ lang != 'en' }" onfocus="{ checkScroll }" />
                                </div>
                            </fieldset>
                        </div>
                    </div>
                </div>
            </div>

            <div show="{tab=='unassigned'}" data-tab="unassigned">
                <div class="uk-panel-box uk-panel-box-primary uk-panel uk-panel-card uk-margin" each="{ moduleData,moduleName in stringsPerModule }" if="{ moduleName == 'unassigned' }">

                    <h3 class="uk-panel-title">@lang('Unassigned strings')</h3>
                    <span class="uk-panel-badge uk-badge uk-badge-notification">{ moduleData.strings.length }</span>

                    <div class="uk-grid uk-grid-small">
                        <div class="uk-width-large-1-2 uk-width-xlarge-1-3 uk-grid-margin" each="{ string,idx in moduleData.strings }" show="{ checkFilterHideCompleted(this) }" if="{ string.substr(0,1) != '@' }">
                            <div class="uk-panel uk-panel-box uk-panel-card strings-box">
                                <fieldset>
                                    <legend if="{ !allowDeletions }">{ string }</legend>
                                    <div class="uk-flex" if="{ allowDeletions }">
                                        <legend class="uk-flex-item-1">{ string }</legend>
                                        <a href="#" class="uk-icon-trash uk-text-danger" onclick="{ deleteUnassignedString }" title="@lang('Delete')"></a>
                                    </div>

                                    <div class="uk-flex uk-flex-middle" each="{ lang in locales }" if="{ lang != 'en' }" show="{ checkLangFilter(lang) }">
                                        <label class="lang_code_label">{ lang }:</label>

                                        <input class="uk-width-1-1 { highlightEmptyStrings && !(knownTranslations[string] && knownTranslations[string][lang]) ? 'uk-form-danger' : '' }" type="text" bind="translations.{moduleName}.{lang}[{escaped(string)}]" onfocus="{ checkScroll }" />
                                    </div>
                                </fieldset>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div show="{tab=='meta'}">
                <div class="uk-panel-box uk-panel-box-primary uk-panel-card uk-margin">

                    <h2 class="uk-panel-title">@lang('meta')</h2>

                    <div class="uk-grid uk-grid-small">

                        <div class="uk-width-medium-1-2 uk-grid-margin">
                            <div class="uk-panel-box uk-panel-card strings-box">
                                <fieldset>
                                    <legend>language</legend>
                                    <div class="uk-flex uk-flex-middle" each="{ lang in locales }" if="{ lang != 'en' }" show="{ checkLangFilter(lang) }">
                                        <label class="lang_code_label">{ lang }:</label>
                                        <input class="uk-width-1-1" type="text" bind="translations.unassigned.{lang}['@meta'].language" />
                                    </div>
                                </fieldset>
                            </div>
                        </div>

                        <div class="uk-width-medium-1-2 uk-grid-margin">
                            <div class="uk-panel-box uk-panel-card strings-box">
                                <fieldset>
                                    <legend>author</legend>
                                    <div class="uk-flex uk-flex-middle" each="{ lang in locales }" if="{ lang != 'en' }" show="{ checkLangFilter(lang) }">
                                        <label class="lang_code_label">{ lang }:</label>
                                        <input class="uk-width-1-1" type="text" bind="translations.unassigned.{lang}['@meta'].author" />
                                    </div>
                                </fieldset>
                            </div>
                        </div>

                        <div class="uk-width-medium-1-2 uk-grid-margin" if="{ modules.includes('Multiplane') }">
                            <div class="uk-panel-box uk-panel-card strings-box">
                                <fieldset>
                                    <legend>@dateformat</legend>
                                    <div class="uk-flex uk-flex-middle" each="{ lang in locales }" if="{ lang != 'en' }" show="{ checkLangFilter(lang) }">
                                        <label class="lang_code_label">{ lang }:</label>
                                        <input type="text" bind="translations.Multiplane.{lang}['@dateformat']" />
                                    </div>
                                </fieldset>
                            </div>
                        </div>

                        <div class="uk-width-medium-1-2 uk-grid-margin" if="{ modules.includes('Multiplane') }">
                            <div class="uk-panel-box uk-panel-card strings-box">
                                <fieldset>
                                    <legend>@dateformat_long</legend>
                                    <div class="uk-flex uk-flex-middle" each="{ lang in locales }" if="{ lang != 'en' }" show="{ checkLangFilter(lang) }">
                                        <label class="lang_code_label">{ lang }:</label>
                                        <input class="uk-width-1-1" type="text" bind="translations.Multiplane.{lang}['@dateformat_long']" />
                                    </div>
                                </fieldset>
                            </div>
                        </div>

                        <div class="uk-width-large-1-2 uk-grid-margin">
                            <div class="uk-panel-box uk-panel-box-secondary uk-panel-card uk-panel-header strings-box">
                                <h3 class="uk-panel-title">
                                    shortdays
                                </h3>
                                <div class="uk-grid uk-grid-small" data-uk-grid-margin>
                                    <div class="uk-width-medium-1-2" each="{ lang in locales }" if="{ lang != 'en' }" show="{ checkLangFilter(lang) }">
                                        <div class="uk-panel-box uk-panel-card">
                                            <fieldset>
                                                <legend>{ lang }:</legend>
                                                <div>
                                                    <div class="uk-flex uk-flex-middle" each="{ label, idx in ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'] }">
                                                        <label class="lang_code_label">{ label }</label>
                                                        <input class="uk-width-1-1" type="text" bind="translations.unassigned.{lang}['@meta'].date.shortdays.{idx}" />
                                                    </div>
                                                </div>
                                            </fieldset>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="uk-width-large-1-2 uk-grid-margin">
                            <div class="uk-panel-box uk-panel-box-secondary uk-panel-card uk-panel-header strings-box">
                                <h3 class="uk-panel-title">
                                    longdays
                                </h3>
                                <div class="uk-grid uk-grid-small" data-uk-grid-margin>
                                    <div class="uk-width-medium-1-2" each="{ lang in locales }" if="{ lang != 'en' }" show="{ checkLangFilter(lang) }">
                                        <div class="uk-panel-box uk-panel-card">
                                            <fieldset>
                                                <legend>{ lang }:</legend>
                                                <div>
                                                    <div class="uk-flex uk-flex-middle" each="{ label, idx in ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'] }">
                                                        <label class="lang_code_label wide">{ label }</label>
                                                        <input class="uk-width-1-1" type="text" bind="translations.unassigned.{lang}['@meta'].date.longdays.{idx}" />
                                                    </div>
                                                </div>
                                            </fieldset>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="uk-width-large-1-2 uk-grid-margin">
                            <div class="uk-panel-box uk-panel-box-secondary uk-panel-card uk-panel-header strings-box">
                                <h3 class="uk-panel-title">
                                    shortmonths
                                </h3>
                                <div class="uk-grid uk-grid-small" data-uk-grid-margin>
                                    <div class="uk-width-medium-1-2" each="{ lang in locales }" if="{ lang != 'en' }" show="{ checkLangFilter(lang) }">
                                        <div class="uk-panel-box uk-panel-card">
                                            <fieldset>
                                                <legend>{ lang }:</legend>
                                                <div>
                                                    <div class="uk-flex uk-flex-middle" each="{ label, idx in ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'] }">
                                                        <label class="lang_code_label wide">{ label }</label>
                                                        <input class="uk-width-1-1" type="text" bind="translations.unassigned.{lang}['@meta'].date.shortmonths.{idx}" />
                                                    </div>
                                                </div>
                                            </fieldset>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="uk-width-large-1-2 uk-grid-margin">
                            <div class="uk-panel-box uk-panel-box-secondary uk-panel-card uk-panel-header strings-box">
                                <h3 class="uk-panel-title">
                                    longmonths
                                </h3>
                                <div class="uk-grid uk-grid-small" data-uk-grid-margin>
                                    <div class="uk-width-medium-1-2" each="{ lang in locales }" if="{ lang != 'en' }" show="{ checkLangFilter(lang) }">
                                        <div class="uk-panel-box uk-panel-card">
                                            <fieldset>
                                                <legend>{ lang }:</legend>
                                                <div>
                                                    <div class="uk-flex uk-flex-middle" each="{ label, idx in ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'] }">
                                                        <label class="lang_code_label wide">{ label }</label>
                                                        <input class="uk-width-1-1" type="text" bind="translations.unassigned.{lang}['@meta'].date.longmonths.{idx}" />
                                                    </div>
                                                </div>
                                            </fieldset>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            </div>

            <div show="{tab=='other'}">
                <div class="uk-panel-box uk-panel-box-primary uk-panel-card uk-margin">

                    <h3 class="uk-panel-title">@lang('Other')</h3>

                    <div class="uk-panel-box uk-panel-card uk-margin">

                        <p>Press this button once to initialize the full potential of the Babel addon</p>
                        <!-- TODO: better info text -->
                        <button type="button" class="uk-button uk-button-primary" onclick="{ restructureI18nFiles }">@lang('Restructure i18n files')</button>
                    </div>

                    <div class="uk-panel-box uk-panel-card uk-margin">
                        <!-- TODO: info text -->
                        <button type="button" class="uk-button uk-button-primary" onclick="{ forceUpdateData }">@lang('Rebuild index from source')</button>
                    </div>
                    <div class="uk-panel-box uk-panel-card uk-margin">
                        <!-- TODO: info text -->
                        <button type="button" class="uk-button uk-button-primary" data-context="true" onclick="{ forceUpdateData }">@lang('Rebuild index from source with context')</button>
                    </div>
<!-- TODO: tinymce i18n files -->
<!-- TODO: date formats with moment.js -->
                </div>
            </div>
        </div>

        <div ref="modal" class="uk-modal">
            <div class="uk-modal-dialog uk-modal-dialog-large">

                <label>@lang('Add new unassigned string')</label>
                <input type="text" class="uk-width-1-1" bind="newString" />

                <div class="uk-modal-footer uk-text-right"><button class="uk-button uk-button-large uk-button-link uk-modal-close">{ App.i18n.get('Close') }</button></div>
            </div>
        </div>

        <cp-actionbar>
            <div class="uk-container uk-container-center">
                <button class="uk-button uk-button-large uk-button-primary">@lang('Save')</button>
                <a class="uk-button uk-button-link" href="@route('/settings')">
                    <span>@lang('Cancel')</span>
                </a>
            </div>
        </cp-actionbar>
    </form>

    <cp-inspectobject ref="inspect"></cp-inspectobject>

    <script type="view/script">

        var $this = this, modal;

        this.mixin(RiotBindMixin);

        this.newString = '';

        this.loading = true;
        this.stringsPerModule = {};
        this.modules = [];

        this.translations = {};

        this.languages = {{ json_encode($languages) }};
        this.locales = this.languages.map(function(el) {return el.code;});

        this.knownTranslations = {{ json_encode($localizedStrings) }};

        this.highlightEmptyStrings = false;
        this.hideCompletedStrings  = false;
        this.allowDeletions        = false;
        this.filterModules = [];
        this.filterLangs = [];

        this.tab = 'modules';

        this.on('mount', function() {

            modal = UIkit.modal(this.refs.modal);
            modal.on({
                'hide.uk.modal': function() {

                    $this.newString = $this.newString.trim()

                    // TODO: check, if new string is a duplicate

                    if ($this.newString) {
                        $this.stringsPerModule['unassigned'].strings.push($this.newString);
                        $this.newString = '';

                        // switch to "unassigned" tab and focus first input of new string
                        $this.tab = 'unassigned';
                        $this.update();
                        var newInput = document.querySelector('div[data-tab=unassigned] .uk-grid > div:last-child input');
                        if (newInput) newInput.focus();
                    }
                }
            });

            App.request('/babel/getTranslatableStrings').then(function(data) {

                if (data) $this.stringsPerModule = data;

                $this.updateStrings();

                $this.loading = false;
                $this.update();
            });

        });

        this.on('bindingupdated', function(args) {

            if (this.tab == 'modules') {
                var matches = args[0].match(/^translations\.(?<module>.*)\.(?<locale>.*)\['(?<string>.*)'\]$/);

                if (matches && matches.groups) {
                    var string = matches.groups.string,
                        locale = matches.groups.locale,
                        value  = args[1];

                    $this.knownTranslations[string] = $this.knownTranslations[string] ?? {};
                    $this.knownTranslations[string][locale] = value;
                }
            }
            else if (this.tab == 'strings') {

                // setTimeout(function() {
                    $this.updateStrings();
                // }, "500");

            }

        });

        updateStrings() {

            this.modules = Object.keys(this.stringsPerModule);
            if (!this.modules.includes('unassigned')) this.modules.push('unassigned');

            $this.stringsPerModule['unassigned'] = $this.stringsPerModule['unassigned'] || {};
            $this.stringsPerModule['unassigned'].strings = $this.stringsPerModule['unassigned'].strings || [];

            // add unassigned strings and @meta keys to virtual "unassigned" module
            Object.keys($this.knownTranslations).forEach(function(string) {

                // add unassigned keys
                var isAssigned = false;
                $this.modules.forEach(function(moduleName) {
                    if (moduleName == 'unassigned') return;
                    if ($this.stringsPerModule[moduleName].strings.includes(string)) {
                        isAssigned = true;
                    }
                });
                if (!isAssigned) {
                    $this.stringsPerModule['unassigned'].strings.push(string);
                }
            });

            // add known strings to "translations"
            this.modules.forEach(function(moduleName) {

                $this.translations[moduleName] = $this.translations[moduleName] || {};

                $this.locales.forEach(function(locale) {
                    if (locale == 'en') return;
                    $this.translations[moduleName][locale] = $this.translations[moduleName][locale] || {};

                    $this.stringsPerModule[moduleName].strings.forEach(function(string) {
                        if ($this.knownTranslations[string] && $this.knownTranslations[string][locale]) {
                            $this.translations[moduleName][locale][string] = $this.knownTranslations[string][locale];
                        }
                    });
                });
            });

        }

        escaped(str) {
            return "'" + str.replace(/(['\[\]])/g,"\\$1") + "'";
        }

        toggleHighlightEmptyStrings(e) {
            if (e) e.preventDefault();
            this.highlightEmptyStrings = !this.highlightEmptyStrings;
        }

        toggleHideNonEmptyStrings(e) {
            if (e) e.preventDefault();
            this.hideCompletedStrings = !this.hideCompletedStrings;
        }

        toggleAllowDeletions(e) {
            if (e) e.preventDefault();
            this.allowDeletions = !this.allowDeletions;
        }

        toggleModuleFilter(e) {
            var module = e.target.dataset.filterModule,
                index  = this.filterModules.indexOf(module);

            if (module == '_all') {
                this.filterModules = [];
                return;
            }

            if (index === -1) {
                this.filterModules.push(module);
            } else {
                this.filterModules.splice(index, 1);
            }

            var allModulesSelected = !(this.modules.filter(el => el !='unassigned' && !this.filterModules.includes(el))).length;
            if (allModulesSelected) {
                this.filterModules = [];
            }
        }

        toggleLangFilter(e) {
            var lang = e.target.dataset.filterLang,
                index  = this.filterLangs.indexOf(lang);

            if (lang == '_all') {
                this.filterLangs = [];
                return;
            }

            if (index === -1) {
                this.filterLangs.push(lang);
            } else {
                this.filterLangs.splice(index, 1);
            }

            var allLanguagesSelected = !(this.locales.filter(el => !this.filterLangs.includes(el))).length;
            if (allLanguagesSelected) {
                this.filterLangs = [];
            }

        }

        checkModuleFilter(moduleName) {
            return !this.filterModules.length | this.filterModules.includes(moduleName);
        }

        checkLangFilter(lang) {
            return !this.filterLangs.length | this.filterLangs.includes(lang);
        }

        toggleTab(e) {
            this.tab = e.target.getAttribute('data-tab');
        }

        showEntryObject() {
            $this.refs.inspect.show($this.translations);
            $this.update();
        }

        submit(e) {

            if (e) e.preventDefault();

            App.request('/babel/save', {data:this.translations}).then(function(data) {

                if (data) {

                    App.ui.notify("Saving successful", "success");

                    $this.translations      = data.translations;
                    $this.knownTranslations = data.dictionaries;

                    $this.updateStrings();
                    $this.update();

                } else {
                    App.ui.notify("Saving failed.", "danger");
                }

            });

        }

        // prevent input fields hiding behind cp-actionbar when navigating with tab key
        checkScroll(e) {

            let viewHeight   = Math.max(document.documentElement.clientHeight, window.innerHeight || 0);
            let footerHeight = document.querySelector('cp-actionbar').offsetHeight;
            let elemBottom   = e.target.getBoundingClientRect().bottom;

            if (elemBottom > (viewHeight - footerHeight)) {
                window.scrollTo({
                    top: window.scrollY + (footerHeight - (viewHeight - elemBottom))
                });
            }

        }

        checkFilterHideCompleted(e) {

            if (!this.hideCompletedStrings) return true;

            // don't hide, if currently editing
            if (e.root.contains(document.activeElement)) return true;

            var needsTranslation = false;
            this.locales.forEach(function(lang) {
                if (lang == 'en') return;
                if (!$this.knownTranslations[e.string] || !$this.knownTranslations[e.string][lang]) {
                    needsTranslation = true;
                }
            });

            return needsTranslation;
        }

        forceUpdateData(e) {

            App.ui.confirm("Are you sure?", function() {

                $this.loading = true;
                $this.update();

                var context = e.target.dataset.context == 'true';
                var options = {
                    force: true,
                    context: context,
                };

                App.request('/babel/getTranslatableStrings', options).then(function(data) {

                    if (data) $this.stringsPerModule = data;

                    $this.updateStrings();

                    $this.loading = false;

                    if (context) {
                        $this.tab = 'modules';
                    }

                    $this.update();
                });
            });
        }

        restructureI18nFiles(e) {

            App.ui.confirm("Are you sure?", function() {

                App.request('/babel/restructureI18nFiles').then(function(data) {

                    if (data) {
                        App.ui.notify(data.join('<br>'));
                    }
                });
            });
        }

        addString() {
            modal.show();
        }

        deleteUnassignedString(e) {
            if (e) e.preventDefault();

            this.stringsPerModule.unassigned.strings.splice(e.item.idx, 1);
            delete this.knownTranslations[e.item.string];
            this.locales.forEach(function(lang) {
                delete $this.translations.unassigned[lang][e.item.string];
            });

        }

    </script>

</div>
