
<!-- Fix targeted elements hiding behind <app-header> (when clicking on toc links in side bar) -->
<style>
    @media (min-width: 768px) {
        :target {
            padding-top: 70px;
            margin-top: -70px;
        }
    }
</style>

<vue-view>

    <template>

        <kiss-container class="kiss-margin-large">

            <ul class="kiss-breadcrumbs">
                <li><a href="<?=$this->route('/babel')?>"><?=t('Babel')?></a></li>
            </ul>

            <app-loader class="kiss-margin-large" v-if="loading"></app-loader>

            <div class="kiss-align-right">
                <a class="kiss-button" @click="showJSON()">
                    <icon class="kiss-margin-small-right">manage_search</icon>
                    <?=t('Json Object')?>
                </a>
            </div>

            <kiss-card v-if="!languages.length">
                <p class=""><?=t('No system languages defined in config file')?></p>
                <?php if ($this->helper('acl')->isSuperAdmin()) { ?>
                <a class="kiss-button" href="<?=$this->route('/finder')?>"><?=t('Edit settings')?></a>
                <p><?=t('Example configuration:')?></p>
<pre><code>return [
    'app.name' => 'My app',
    'babel' => [
        'languages' => [
            'de' => 'Deutsch',
            'fr' => 'Francais',
        ],
    ],
];</code></pre>
                <?php } ?>
            </kiss-card>

            <div class="kiss-margin-large" v-if="!loading && languages.length">

                <div class="kiss-margin" v-for="moduleName in modules">

                    <div class="kiss-flex kiss-flex-top">
                        <h3 class="" :id="'babel_toc_'+moduleName.toLowerCase()">{{ moduleName }}</h3>
                        <div class="kiss-flex-1 kiss-margin-left"></div>
                        <span class="kiss-badge">{{ stringsPerModule[moduleName].strings.length }}</span>
                    </div>

                    <kiss-grid cols="2@xl 3@xxl">

                        <kiss-card class="kiss-padding" theme="contrast shadowed" v-for="string,idx in stringsPerModule[moduleName].strings">
                            <fieldset class="kiss-fieldset" v-if="string != '@meta'">
                                <legend class="kiss-legend">{{ string }}</legend>

                                <div class="" v-for="lang in locales" v-if="lang != 'en'">
                                    <label class="">{{ lang }}:</label>

                                    <input class="kiss-input" type="text" :value="translations[moduleName][lang][string]" @change="updateTranslationsFromInput($event, lang, string)" @focus="checkScroll" />

                                </div>
                            </fieldset>
                            <div v-if="string == '@meta'">
                                <h4>@meta</h4>
                                <fieldset class="kiss-fieldset">
                                    <legend class="kiss-legend">language</legend>
                                    <div class="" v-for="lang in locales" v-if="lang != 'en'">
                                        <label class="">{{ lang }}:</label>
                                        <input class="kiss-input" type="text" v-model.trim="translations.unassigned[lang]['@meta'].language" />
                                    </div>
                                </fieldset>
                                <fieldset class="kiss-fieldset">
                                    <legend class="kiss-legend">author</legend>
                                    <div class="" v-for="lang in locales" v-if="lang != 'en'">
                                        <label class="">{{ lang }}:</label>
                                        <input class="kiss-input" type="text" v-model.trim="translations.unassigned[lang]['@meta'].author" />
                                    </div>
                                </fieldset>
                                <p>TODO: date/week/months strings</p>
                                <p>TODO: cleaner UI for @meta strings</p>
                            </div>

                        </kiss-card>
                    </kiss-grid>
                </div>

            </div>
        </kiss-container>

        <app-actionbar>

            <kiss-container>
                <div class="kiss-flex kiss-flex-middle">
<!--                    <div class="kiss-button-group">
                        <a class="kiss-button" @click="addString()">
                            <?=t('Add string')?>
                        </a>
                    </div>-->
                    <div class="kiss-flex-1"></div>
                    <div class="kiss-button-group">
                        <a class="kiss-button" href="<?=$this->route("/system")?>">
                            <span><?=t('Cancel')?></span>
                        </a>
                        <a class="kiss-button kiss-button-primary" @click="submit()">
                            <span><?=t('Save')?></span>
                        </a>
                    </div>
                </div>
            </kiss-container>

        </app-actionbar>

    </template>

    <script type="module">

        export default {
            data() {
                return {
                    loading: true,
                    stringsPerModule: {},
                    modules: <?=json_encode($modules)?>,

                    translations: {},

                    languages: <?=json_encode($languages)?>,
                    locales: [],

                    knownTranslations: <?=json_encode($localizedStrings)?>,

                    // highlightEmptyStrings : false,
                    // hideCompletedStrings  : false,
                    // allowDeletions        : false,
                    // filterModules: [],
                    // filterLangs: [],

                    // tab: 'modules',

                }
            },

            computed: {
            },

            mounted() {

                this.locales = this.languages.map(function(el) {return el.code;});

                this.load();
            },

            methods: {

                load() {

                    this.loading = true;

                    this.$request('/babel/getTranslatableStrings').then(data => {

                        this.stringsPerModule = data;

                        this.initStrings();

                        this.loading = false;
                    })
                },

                initStrings() {

                    this.stringsPerModule['unassigned'] = this.stringsPerModule['unassigned'] || {};
                    this.stringsPerModule['unassigned'].strings = this.stringsPerModule['unassigned'].strings || [];

                    // add unassigned strings and @meta keys to virtual "unassigned" module
                    Object.keys(this.knownTranslations).forEach(string => {

                        // add unassigned keys
                        var isAssigned = false;
                        this.modules.forEach(moduleName => {
                            if (moduleName == 'unassigned') return;
                            if (this.stringsPerModule[moduleName].strings.includes(string)) {
                                isAssigned = true;
                            }
                        });
                        if (!isAssigned) {
                            this.stringsPerModule['unassigned'].strings.push(string);
                        }
                    });

                    // add known strings to "translations"
                    this.modules.forEach(moduleName => {

                        this.translations[moduleName] = this.translations[moduleName] || {};

                        this.locales.forEach(locale => {
                            if (locale == 'en') return;
                            this.translations[moduleName][locale] = this.translations[moduleName][locale] || {};

                            this.stringsPerModule[moduleName].strings.forEach(string => {
                                if (this.knownTranslations[string]
                                    && this.knownTranslations[string].hasOwnProperty(locale)) {

                                    this.translations[moduleName][locale][string] = this.knownTranslations[string][locale];
                                }
                            });
                        });
                    });

                },

                updateTranslationsFromInput(e, locale, string) {

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

                },

                listModulesContainingString(str, moduleName = null) {

                    return this.modules.filter(module => {
                        if (moduleName && module == moduleName) return false;
                        return this.stringsPerModule[module].strings.includes(str);
                    });

                },

                showJSON() {
                    VueView.ui.offcanvas('system:assets/dialogs/json-viewer.js', {data: this.translations}, {}, {flip: true, size: 'large'})
                },

                // addString() {
                //     console.log('open modal to add new string');
                //     // modal.show();
                // },

                submit(e) {

                    if (e) e.preventDefault();

                    this.$request('/babel/save', {data:this.translations}).then(data => {

                        if (data) {

                            App.ui.notify("Saving successful", "success");

                            this.translations      = data.translations;
                            this.knownTranslations = data.dictionaries;

                            this.initStrings();

                        } else {
                            App.ui.notify("Saving failed.", "danger");
                        }

                    });

                },

                // prevent input fields hiding behind app-actionbar when navigating with tab key
                checkScroll(e) {

                    let viewHeight   = Math.max(document.documentElement.clientHeight, window.innerHeight || 0);
                    let footerHeight = document.querySelector('app-actionbar').offsetHeight;
                    let elemBottom   = e.target.getBoundingClientRect().bottom;

                    if (elemBottom > (viewHeight - footerHeight)) {
                        window.scrollTo({
                            top: window.scrollY + (footerHeight - (viewHeight - elemBottom))
                        });
                    }

                },

            }
        }
    </script>
</vue-view>



<?php $this->start('app-side-panel') ?>

<h2 class="kiss-size-4"><?=t('Babel')?></h2>
<kiss-navlist>
    <ul>
    <?php foreach ($modules as $module): ?>
        <li>
            <a class="kiss-link-muted kiss-flex kiss-flex-middle" href="#babel_toc_<?=strtolower($module)?>">
                <?=$module?>
            </a>
        </li>
    <?php endforeach ?>
    </ul>
</kiss-navlist>

<div class="kiss-margin kiss-visible@m">


</div>

<?php $this->end('app-side-panel') ?>
