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

        #babel-filters {
            position: fixed;
            top: 0;
            left: 0;
            bottom: 0;
            width: 20em;
            max-width: 100%;
            z-index: 1000; /* <app-header> has 980 */
            background-color: rgba(255,255,255,0.95);
            padding: 1em;
            box-shadow: 0 1px 2px 0 rgba(0,0,0,0.08);
            box-sizing: border-box;
        }

        .button-blank {
            background: none;
            border: none;
            padding: .5em;
            min-height: auto;
            font-size: 1em;
        }

        .module-buttons {
            line-height: 2em;
        }
        .babel-button {
            line-height: 1.8em;
        }
    </style>

    <form class="uk-form uk-container uk-container-center" onsubmit="{ submit }">

        <div class="uk-panel-box uk-panel-card" if="{ !languages.length }">
            <p class="uk-alert uk-alert-large">@lang('No system languages defined in config file')</p>
            @if($app->module('cockpit')->isSuperAdmin())
            <a class="uk-button uk-button-primary" href="@route('settings/edit')">@lang('Edit settings')</a>
            <p>@lang('Example configuration:')</p>
<pre><code>return [
    'app.name' => 'My app',
    'babel' => [
        'languages' => [
            'de' => 'Deutsch',
            'fr' => 'Francais',
        ],
    ],
];</code></pre>
            @endif
        </div>

        <div class="uk-margin">
            <ul class="uk-tab uk-margin-large-bottom">
                <li class="{ tab=='modules' && 'uk-active'}"><a class="" onclick="{ toggleTab }" data-tab="modules">@lang('Modules')</a></li>
                <li class="{ tab=='strings' && 'uk-active'}"><a class="" onclick="{ toggleTab }" data-tab="strings">@lang('All strings')</a></li>
                <li class="{ tab=='meta' && 'uk-active'}"><a class="" onclick="{ toggleTab }" data-tab="meta">@lang('Meta')</a></li>
                <li class="{ tab=='other' && 'uk-active'}"><a class="" onclick="{ toggleTab }" data-tab="other">@lang('Other')</a></li>
                <li class=""><a class="" onclick="{ showEntryObject }">@lang('JSON')</a></li>
            </ul>
        </div>

        <div id="babel-filters" class="uk-hidden">

            <div class="uk-panel">

                <button type="button" class="uk-icon-close uk-panel-badge uk-button button-blank" onclick="{ toggleFilterMenu }" aria-label="@lang('Close')"></button>

                <button type="button" class="uk-button uk-icon-{ layout == 'grid' ? 'th' : 'list' }" onclick="{ toggleLayout }" title="@lang('Toggle layout')" data-uk-tooltip>
                </button>

                <button type="button" class="uk-button uk-button-small uk-button-{ highlightEmptyStrings ? 'success' : 'primary' } uk-margin-small babel-button" onclick="{ toggleHighlightEmptyStrings }">@lang('Highlight empty fields')</button>
                <button type="button" class="uk-button uk-button-small uk-button-{ hideCompletedStrings ? 'success' : 'primary' } uk-margin-small babel-button" onclick="{ toggleHideNonEmptyStrings }">@lang('Hide completed translations')</button>
                <button type="button" class="uk-button uk-button-small uk-button-{ allowDeletions ? 'success' : 'primary' } uk-margin-small babel-button" onclick="{ toggleAllowDeletions }">@lang('Allow deletions')</button>

                <div class="uk-margin">
                    @lang('Languages:')
                    <span class="module-buttons">
                        <button type="button" data-filter-lang="_all" onclick="{ toggleLangFilter }" class="uk-button uk-button-small" disabled="{ !filterLangs.length }">@lang('All')</button>
                        <button type="button" data-filter-lang="{ lang.code }" class="uk-button uk-button-small uk-margin-small-right { filterLangs.length && checkLangFilter(lang.code) ? 'uk-button-success' : '' }" title="{ lang.name }" each="{ lang in languages }" if="{ lang.code != 'en' }" onclick="{ toggleLangFilter }" data-uk-tooltip>
                            { lang.code }
                        </button>
                    </span>
                </div>

                <div class="uk-margin" show="{ tab == 'modules' }">
                    @lang('Modules:')
                    <span class="module-buttons">
                        <button type="button" data-filter-module="_all" onclick="{ toggleModuleFilter }" class="uk-button uk-button-small" disabled="{ !filterModules.length }">@lang('All')</button>
                        <button type="button" data-filter-module="{ moduleName }" onclick="{ toggleModuleFilter }" class="uk-button uk-button-small uk-margin-small-right { filterModules.length && checkModuleFilter(moduleName) ? 'uk-button-success' : '' }" each="{ moduleName in modules }">
                            { moduleName == 'unassigned' ? App.i18n.get('Unassigned') : moduleName }
                        </button>
                    </span>
                </div>

            </div>
        </div>

        <div class="uk-text-center" show="{ loading }">
            <span class="uk-icon-spinner uk-icon-spin"></span>
        </div>

        <div if="{ !loading && languages.length }">

            <div show="{ tab == 'modules' }">
                <div class="uk-panel-box uk-panel-box-primary uk-panel uk-panel-card uk-margin" each="{ moduleName in modules }" show="{ checkModuleFilter(moduleName) }" data-module="{ moduleName }">

                    <h3 class="uk-panel-title">{ moduleName != 'unassigned' ? moduleName : App.i18n.get('Unassigned strings') }</h3>
                    <span class="uk-panel-badge uk-badge uk-badge-notification">{ stringsPerModule[moduleName].strings.length }</span>

                    <div class="uk-grid uk-grid-small uk-grid-match">

                        <div class="uk-width-1-1 { layout == 'grid' && 'uk-width-large-1-2 uk-width-xlarge-1-3' } uk-grid-margin" each="{ string,idx in stringsPerModule[moduleName].strings }" show="{ checkFilterHideCompleted(this) }" if="{ string.substr(0,1) != '@' }">
                            <div class="uk-panel-box uk-panel-card strings-box uk-panel-header strings-box">
                                <fieldset>
                                    <legend class="uk-panel-title" if="{ !allowDeletions || moduleName != 'unassigned' }">{ string }</legend>
                                    <div class="uk-flex" if="{ allowDeletions && moduleName == 'unassigned' }">
                                        <legend class="uk-flex-item-1">{ string }</legend>
                                        <a href="#" class="uk-icon-trash uk-text-danger" onclick="{ deleteUnassignedString }" title="@lang('Delete')"></a>
                                    </div>

                                    <div class="uk-flex uk-flex-middle" each="{ lang in locales }" if="{ lang != 'en' }" show="{ checkLangFilter(lang) }">
                                        <label class="lang_code_label">{ lang }:</label>

                                        <input class="uk-width-1-1 { highlighted(string,lang) && 'uk-form-danger' }" type="text" value="{ translations[moduleName][lang][string] }" onchange="{ updateTranslationsFromInput.bind(null, string, lang) }" onfocus="{ checkScroll }" />
                                    </div>
                                </fieldset>

                                { (listOfAffectedModules = listModulesContainingString(string, moduleName)) && '' }
                                <div class="uk-panel-box-footer" if="{ stringsPerModule[moduleName].context && stringsPerModule[moduleName].context[idx] || listOfAffectedModules.length }">
                                    <div class="uk-text-small" if="{ listOfAffectedModules.length }">
                                        @lang('affects:')
                                        { listOfAffectedModules.join(', ') }
                                    </div>
                                    <div class="" if="{ stringsPerModule[moduleName].context && stringsPerModule[moduleName].context[idx] }">
                                        <span>@lang('context:')</span>
                                        <ul class="uk-list">
                                            <li each="{ file in stringsPerModule[moduleName].context[idx] }">
                                                <code>{ file }</code>
                                            </li>
                                        </ul>
                                    </div>
                                </div>

                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div show="{ tab == 'strings' }">
                <div class="uk-panel-box uk-panel-box-primary uk-panel uk-panel-card">

                    <h3 class="uk-panel-title">@lang('All strings')</h3>
                    <span class="uk-panel-badge uk-badge uk-badge-notification">{ Object.keys(knownTranslations).length }</span>

                    <div class="uk-grid uk-grid-small uk-grid-match">

                        <div class="uk-width-1-1 { layout == 'grid' && 'uk-width-large-1-2 uk-width-xlarge-1-3' } uk-grid-margin" each="{ translations,string in knownTranslations }" if="{ string.substr(0,1) != '@' }" show="{ checkFilterHideCompleted(this) }">
                            <div class="uk-panel-box uk-panel-card uk-panel-header strings-box">

                                { (listOfAffectedModules = listModulesContainingString(string, 'unassigned')) && '' }

                                <fieldset>

                                    <legend class="uk-panel-title" if="{ !allowDeletions }">{ string }</legend>

                                    <div class="uk-flex" if="{ allowDeletions }">
                                        <legend class="uk-flex-item-1">{ string }</legend>
                                        <a href="#" class="uk-icon-trash uk-text-danger" onclick="{ deleteUnassignedString }" title="@lang('Delete')" if="{ !listOfAffectedModules.length }"></a>
                                    </div>

                                    <div class="uk-flex uk-flex-middle" each="{ lang in locales }" show="{ checkLangFilter(lang) }">
                                        <label class="lang_code_label" if="{ lang != 'en' }">{ lang }:</label>

                                        <input class="uk-width-1-1 { highlighted(string,lang) && 'uk-form-danger' }" type="text" value="{ knownTranslations[string][lang] }" onchange="{ updateTranslationsFromInput.bind(null, string, lang) }" if="{ lang != 'en' }" onfocus="{ checkScroll }" />
                                    </div>
                                </fieldset>
                                <div class="uk-panel-box-footer" if="{ listOfAffectedModules.length }">
                                    <div class="uk-text-small" if="{ listOfAffectedModules.length }">
                                        @lang('affects:')
                                        { listOfAffectedModules.join(', ') }
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div show="{ tab == 'meta' }">
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

            <div show="{ tab == 'other' }">
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
                <input ref="newString" type="text" class="uk-width-1-1" />

                <div class="uk-modal-footer uk-text-right"><button class="uk-button uk-button-large uk-button-link uk-modal-close">{ App.i18n.get('OK') }</button></div>
            </div>
        </div>

        <cp-actionbar>
            <div class="uk-container uk-container-center uk-flex uk-flex-middle">
                <button class="uk-button uk-button-large uk-button-primary">@lang('Save')</button>
                <a class="uk-button uk-button-link" href="@route('/settings')">
                    <span>@lang('Cancel')</span>
                </a>

                <button type="button" class="uk-button uk-button-large" title="@lang('Filter')" onclick="{ toggleFilterMenu }"><i class="uk-icon-filter uk-text-large uk-text-middle"></i></button>

                <div class="uk-flex-item-1"></div>
                <button type="button" class="uk-button uk-button-large" onclick="{ openModal }" title="@lang('Add string')"><i class="uk-icon-plus uk-text-large uk-text-middle"></i></button>
            </div>
        </cp-actionbar>
    </form>

    <cp-inspectobject ref="inspect"></cp-inspectobject>

    <script type="view/script">

        var $this = this, modal;

        this.mixin(RiotBindMixin);

        this.loading = true;
        this.stringsPerModule = {};
        this.modules = {{ json_encode($modules) }};

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
        this.layout = 'grid';

        this.on('mount', function() {

            modal = UIkit.modal(this.refs.modal);
            modal.on({
                'hide.uk.modal': function() {
                    $this.addString();
                }
            });

            App.request('/babel/getTranslatableStrings').then(function(data) {

                if (data) $this.stringsPerModule = data;

                $this.initStrings();

                $this.loading = false;
                $this.update();
            });

        });

        updateTranslationsFromInput(string, locale, e) {

            let value = e.target.value.trim();
            e.target.value = value;

            this.knownTranslations[string] = this.knownTranslations[string] ?? {};
            this.knownTranslations[string][locale] = value;

            let affectedModules = this.listModulesContainingString(string);
            if (affectedModules.length) {
                affectedModules.forEach(m => {
                    this.translations[m][locale][string] = value;
                });
            }

        }

        initStrings() {

            this.stringsPerModule['unassigned'] = {
                strings: []
            };

            // add unassigned strings and @meta keys to virtual "unassigned" module
            Object.keys(this.knownTranslations).forEach(function(string) {

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

                        if ($this.knownTranslations[string]
                            && $this.knownTranslations[string].hasOwnProperty(locale)) {

                            $this.translations[moduleName][locale][string] = $this.knownTranslations[string][locale];
                        }
                    });
                });
            });

        }

        // escape string for riot.js binding
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

                    $this.initStrings();
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

                    $this.initStrings();

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

        openModal() {
            modal.show();
        }

        addString() {

            let newString = this.refs.newString.value.trim();
            this.refs.newString.value = '';

            if (newString) {

                let isDuplicate = !!(this.listModulesContainingString(newString)).length;

                if (isDuplicate) {
                    App.ui.notify('String exists already', 'danger');
                    return;
                }

                this.stringsPerModule['unassigned'].strings.push(newString);

                // switch to "unassigned" module and focus first input of new string
                this.tab = 'modules';
                this.update();
                var newInput = document.querySelector('div[data-module=unassigned] .uk-grid > div:last-child input');
                if (newInput) newInput.focus();
            }

        }

        deleteUnassignedString(e) {
            if (e) e.preventDefault();

            // modules tab has item.idx, strings tab doesn't
            var idx = e.item.idx || this.stringsPerModule.unassigned.strings.indexOf(e.item.string);

            this.stringsPerModule.unassigned.strings.splice(idx, 1);
            delete this.knownTranslations[e.item.string];
            this.locales.forEach(function(lang) {
                delete $this.translations.unassigned[lang][e.item.string];
            });

        }

        listModulesContainingString(str, moduleName = null) {

            return this.modules.filter(module => {
                if (moduleName && module == moduleName) return false;
                return this.stringsPerModule[module].strings.includes(str);
            });

        }

        toggleLayout(e) {
            if (e) e.preventDefault();
            this.layout = this.layout == 'grid' ? 'list' : 'grid';
        }

        highlighted(string, lang) {
            if (!this.highlightEmptyStrings) return false;
            return !(this.knownTranslations[string] && this.knownTranslations[string][lang]);
        }

        toggleFilterMenu() {
            document.querySelector('#babel-filters').classList.toggle('uk-hidden');
        }

    </script>

</div>
