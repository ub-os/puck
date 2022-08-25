import {$, $$} from '../General/Aliases';

// CLASS ListFilter
export default class ListFilter {
    constructor(node, options= {}, categories= {}, t3langData = {}) {
        this.node = node;
        this.options = {
            ...{setTriggerPotential: true},
            ...options};
        this.urlParams = new URLSearchParams(window.location.search);
        this.htmlLang = $('html').lang.substring(0,2);
        this.currentLang = t3langData[this.htmlLang] ? t3langData[this.htmlLang] : t3langData.en;
        this.activeTriggerCounter = 0;
        this.list = $(`[data-menu-filter-list-of="${node.id}"]`);
        this.clearAllTrigger = node.$('[data-menu-filter-clear-all]');
        this.activeTriggerDisplay =  node.$('[data-menu-filter-trigger-counter]');
        this.state = this.createStateCategories(categories);
        this.items = this.createItems();
        this.triggers = this.createTriggers();
        this.watch();
    }
    arrayIsSubset(arr1, arr2) {
        return arr1.every(val => arr2.includes(val));
    }
    createStateCategories(categories) {
        let state = {};
        for (let id in categories) {
            state[id] = {
                ...{conjunction_eval:false,
                    exclusive:false,
                    selected:this.getStateFromParam(id)},
                ...categories[id]};
        }
        return state;
    }
    createItems() {
        let items = [];
        $$(`[data-menu-filter-item-of="${this.node.id}"]`).forEach((item,i) => {
            items[i] = {
                data: JSON.parse(item.$('[data-menu-filter-item-data]').textContent),
                node: item,
                active: true
            };
        });
        return items;
    }
    createTriggers() {
        let triggers = {};
        const self = this;
        this.node.$$(`[data-menu-filter-trigger]`).forEach((trigger,i) => {
            let trVal = trigger.getAttribute('data-menu-filter-trigger');
            let tr = {
                category: trVal.split(':')[0],
                value: trVal.split(':')[1],
                valArr: trVal.split(':')[1].split(','),
                node: trigger,
                potential: [],
                active: false,
                enabled: true
            };
            if (self.arrayIsSubset(tr.valArr, self.state[tr.category].selected)) {
                self.toggleTrigger(tr);
            }
            self.setTriggerPotential(tr);
            tr.node.addEventListener('click', event => {
                self.trigger(tr);
            });
            triggers[trVal] = tr;
        });
        return triggers;
    }
    trigger(tr){
        const self = this;
        self.toggleTrigger(tr);
        if (self.state[tr.category].exclusive) {
            for (let activeTriggerInCat of Object.entries(self.triggers).filter(item => (item[1].category == tr.category && item[1].active && item[1].value != tr.value))) {
                self.toggleTrigger(activeTriggerInCat[1]);
            }
        }
        self.setCategoryParam(tr.category);
        for (let trId in self.triggers) {
            self.setTriggerPotential(self.triggers[trId]);
        }
        self.filterItems();
    }
    toggleTrigger(tr) {
        if (tr.active) {
            tr.active = false;
            tr.node.classList.remove('-active');
            this.changeActiveTriggerCounter(-1);
        } else {
            tr.active = true;
            tr.node.classList.add('-active');
            this.changeActiveTriggerCounter(1);
        }
        this.state = this.getChangedState(tr, this.state);
    }
    getChangedState(tr, state, invertActiveState = false) {
        const self = this;
        let st = JSON.parse(JSON.stringify(state));
        if ((tr.active && !invertActiveState) || (!tr.active && invertActiveState)) {
            if (!self.arrayIsSubset(tr.valArr, st[tr.category].selected)) {
                st[tr.category].selected.push.apply(st[tr.category].selected, tr.valArr);
            }
        } else {
            st[tr.category].selected = st[tr.category].selected.filter(item => !tr.valArr.includes(item));
        }
        return st;
    }
    setCategoryParam(cat) {
        const self = this;
        self.urlParams.set(
            self.t3translate(cat).toLowerCase(),
            self.state[cat].selected
                .map(x => self.t3translate(x).toLowerCase())
                .join('.'));
        if (!self.state[cat].selected.length) {
            self.urlParams.delete(self.t3translate(cat).toLowerCase());
        }
        window.history.replaceState({}, '', `${window.location.pathname}?${self.urlParams}`);
    }
    getStateFromParam(cat) {
        return this.urlParams.get(this.t3translate(cat).toLowerCase()) ? this.urlParams.get(this.t3translate(cat).toLowerCase()).split('.').map(x => this.t3transKey(x)) : [];
    }
    filterItems() {
        const self = this;
        self.list.animate([
            { opacity: '0' },
            { opacity: '1' },
        ], {
            duration: 550,
            iterations: 1
        });
        self.items.forEach(item => {
            self.toggleItem(item, self.checkItem(item, self.state));
        });
    }
    checkItem(item, state) {
        const self = this;
        let check = true;
        for (let cat in state) {
            if (state[cat].conjunction_eval) {
                if (state[cat].selected.length && !self.arrayIsSubset(state[cat].selected, item.data[cat].split(','))) {
                    check = false;
                    break;
                }
            } else if (state[cat].selected.length && !state[cat].selected.includes(item.data[cat])) {
                check = false;
                break;
            }
        }
        return check;
    }
    toggleItem(item, check) {
        if (check) {
            item.node.classList.add('-filter-1');
            item.node.classList.remove('-filter-0','u-hide');
            item.active = true;
        } else {
            item.node.classList.remove('-filter-1');
            item.node.classList.add('-filter-0','u-hide');
            item.active = false;
        }
    }
    setTriggerPotential(tr) {
        const self = this;
        if (self.options.setTriggerPotential) {
            let state = {};
            if (self.state[tr.category].conjunction_eval || tr.active) {
                state = self.getChangedState(tr, self.state, true);
            } else {
                state = JSON.parse(JSON.stringify(self.state));
                state[tr.category].selected = tr.valArr;
            }
            tr.enabled = false;
            self.items.forEach((item, i) => {
                let check = self.checkItem(item, state);
                tr.potential[i] = check;
                if (check) { tr.enabled = true; }
            });
            if (tr.enabled) {
                tr.node.classList.add('-enabled');
                tr.node.classList.remove('-disabled');
            } else {
                tr.node.classList.remove('-enabled');
                tr.node.classList.add('-disabled');
            }
        }
    }
    changeActiveTriggerCounter(addend) {
        const self = this;
        self.activeTriggerCounter += addend;
        self.activeTriggerDisplay.textContent = self.activeTriggerCounter;
        if (self.activeTriggerCounter > 0) {
            self.clearAllTrigger.classList.add('-active');
            self.clearAllTrigger.classList.remove('-disabled');
        } else {
            self.clearAllTrigger.classList.remove('-active');
            self.clearAllTrigger.classList.add('-disabled');
        }
    }
    clearAll() {
        const self = this;
        for (let trId in self.triggers) {
            if (self.triggers[trId].active) {
                self.toggleTrigger(self.triggers[trId]);
            }
        }
        for (let trId in self.triggers) {
            self.setTriggerPotential(self.triggers[trId]);
        }
        for (let cat in self.state) {
            self.setCategoryParam(cat);
        }
        self.filterItems();
    }
    t3translate(key) {
        if (this.currentLang) {
            let k = this.currentLang[key];
            if (k) {
                return (k.target) ? k.target : k.source;
            }
        }
        return key;
    }
    t3transKey(trans) {
        let k = trans;
        const self = this;
        if (self.currentLang) {
            for (let key in self.currentLang) {
                let x = self.currentLang[key];
                if (x.source.toLowerCase() == trans || (x.target && x.target.toLowerCase() == trans)) {
                    k = key;
                }
            }
        }
        return k;
    }
    watch() {
        const self = this;
        this.changeActiveTriggerCounter(0);
        this.clearAllTrigger.addEventListener('click', event => {
            event.stopPropagation();
            self.clearAll();
        });
        this.filterItems();
    }
}

// HTML Structure
//  #{id}[data-menu-filter]
//      [data-menu-filter-clear-all]
//      [data-menu-filter-trigger-counter]
//      [data-menu-filter-trigger={filterCategory}:{value}]
//      ..
//  [data-menu-filter-list-of={id}]
//      [data-menu-filter-item-of={id}]
//          script[data-menu-filter-item-data]
//              {"{filterCategory}":"{value}"}
//      ..
// Example HTML
/*<div data-menu-filter id="filterId">
    <button data-menu-filter-clear-all>Clear</button>
    <span data-menu-filter-trigger-counter>0</span>

    <header>Animal</header>
    <button data-menu-filter-trigger="animal:cat">Cat</button>
    <button data-menu-filter-trigger="animal:dog">Dog</button>

    <header>Clothing</header>
    <button data-menu-filter-trigger="clothing:shirt">Shirt</button>
    <button data-menu-filter-trigger="clothing:pants">Pants</button>
</div>
<ul data-menu-filter-list-of="filterId">
    <li data-menu-filter-item-of="filterId">
        <script data-menu-filter-item-data
                type="application/json">{"animal":"cat","clothing":"shirt"}</script>
        Cat with shirt
    </li>
    <li data-menu-filter-item-of="filterId">
        <script data-menu-filter-item-data
                type="application/json">{"animal":"dog","clothing":"shirt,pants"}</script>
        Dog with pants and shirt
    </li>
</ul>*/
