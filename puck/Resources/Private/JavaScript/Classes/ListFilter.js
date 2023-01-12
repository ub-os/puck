import { $, $$, jsx } from '../General/Aliases';
import {getNode} from '../General/Functions';

// CLASS ListFilter
export default class ListFilter {
    constructor(
        target,
        {
            listNode,
            clearAllNode, 
            counterNode, 
            options= {},
            categories= {}, 
            t3langData = {},
        }) {
        this.node = getNode(target, 'ListFilter');
        Object.assign(this, {
            id: this.node.id,
            options: {
                ...{setTriggerPotential: true},
                ...options},
            listNode: listNode || this.node.$('[data-filter-list]'),
            clearAllNode: clearAllNode || this.node.$('[data-filter-clear-all]'),
            counterNode: counterNode || this.node.$('[data-filter-counter]'),
            activeTriggerCounter: 0,
            htmlLang: document.documentElement.lang.substring(0,2),
            urlParams: new URLSearchParams(window.location.search),
            currentLang: t3langData[this.htmlLang] ? t3langData[this.htmlLang] : t3langData.en,
            state: this.createStateCategories(categories),
            items: this.createItems(),
            triggers: this.createTriggers(),
        })
    }
    arrayIsSubset(arr1, arr2) {
        return arr1.every(val => arr2.includes(val));
    }
    createStateCategories(categories) {
        let state = {};
        for (let id in categories) {
            state[id] = {
                ...{conjunctionEval:false,
                    exclusive:false,
                    selected:this.getStateFromParam(id)},
                ...categories[id]};
        }
        return state;
    }
    createItems() {
        let items = [];
        $$(`[data-filter-item-of="${this.node.id}"]`).forEach((item,i) => {
            items[i] = {
                data: JSON.parse(item.$('[data-filter-item-data]').textContent),
                node: item,
                active: true
            };
        });
        return items;
    }
    createTriggers() {
        let triggers = {};
        this.node.$$(`[data-filter-trigger]`).forEach((trigger,i) => {
            let trVal = trigger.getAttribute('data-filter-trigger');
            let tr = {
                category: trVal.split(':')[0],
                value: trVal.split(':')[1],
                valArr: trVal.split(':')[1].split(','),
                node: trigger,
                potential: [],
                active: false,
                enabled: true
            };
            if (this.arrayIsSubset(tr.valArr, this.state[tr.category].selected)) {
                this.toggleTrigger(tr);
            }
            this.setTriggerPotential(tr);
            tr.node.addEventListener('click', event => {
                this.trigger(tr);
            });
            triggers[trVal] = tr;
        });
        return triggers;
    }
    trigger(tr){
        this.toggleTrigger(tr);
        if (this.state[tr.category].exclusive) {
            for (let activeTriggerInCat of Object.entries(this.triggers).filter(item => (item[1].category == tr.category && item[1].active && item[1].value != tr.value))) {
                this.toggleTrigger(activeTriggerInCat[1]);
            }
        }
        this.setCategoryParam(tr.category);
        for (let trId in this.triggers) {
            this.setTriggerPotential(this.triggers[trId]);
        }
        this.filterItems();
    }
    toggleTrigger(tr) {
        if (tr.active) {
            tr.active = false;
            tr.node.classList.remove('--active');
            this.changeActiveTriggerCounter(-1);
        } else {
            tr.active = true;
            tr.node.classList.add('--active');
            this.changeActiveTriggerCounter(1);
        }
        this.state = this.getChangedState(tr, this.state);
    }
    getChangedState(tr, state, invertActiveState = false) {
        let st = JSON.parse(JSON.stringify(state));
        if ((tr.active && !invertActiveState) || (!tr.active && invertActiveState)) {
            if (!this.arrayIsSubset(tr.valArr, st[tr.category].selected)) {
                st[tr.category].selected.push.apply(st[tr.category].selected, tr.valArr);
            }
        } else {
            st[tr.category].selected = st[tr.category].selected.filter(item => !tr.valArr.includes(item));
        }
        return st;
    }
    setCategoryParam(cat) {
        this.urlParams.set(
            this.t3translate(cat).toLowerCase(),
            this.state[cat].selected
                .map(x => this.t3translate(x).toLowerCase())
                .join('.'));
        if (!this.state[cat].selected.length) {
            this.urlParams.delete(this.t3translate(cat).toLowerCase());
        }
        window.history.replaceState({}, '', `${window.location.pathname}?${this.urlParams}`);
    }
    getStateFromParam(cat) {
        return this.urlParams.get(this.t3translate(cat).toLowerCase()) ? this.urlParams.get(this.t3translate(cat).toLowerCase()).split('.').map(x => this.t3transKey(x)) : [];
    }
    filterItems() {
        this.listNode.animate([
            { opacity: '0' },
            { opacity: '1' },
        ], {
            duration: 550,
            iterations: 1
        });
        this.items.forEach(item => {
            this.toggleItem(item, this.checkItem(item, this.state));
        });
    }
    checkItem(item, state) {
        let check = true;
        for (let cat in state) {
            if (state[cat].conjunctionEval) {
                if (state[cat].selected.length && !this.arrayIsSubset(state[cat].selected, item.data[cat].split(','))) {
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
            item.node.classList.add('--filter-1');
            item.node.classList.remove('--filter-0','u-hide');
            item.active = true;
        } else {
            item.node.classList.remove('--filter-1');
            item.node.classList.add('--filter-0','u-hide');
            item.active = false;
        }
    }
    setTriggerPotential(tr) {
        if (this.options.setTriggerPotential) {
            let state = {};
            if (this.state[tr.category].conjunctionEval || tr.active) {
                state = this.getChangedState(tr, this.state, true);
            } else {
                state = JSON.parse(JSON.stringify(this.state));
                state[tr.category].selected = tr.valArr;
            }
            tr.enabled = false;
            this.items.forEach((item, i) => {
                let check = this.checkItem(item, state);
                tr.potential[i] = check;
                if (check) { tr.enabled = true; }
            });
            if (tr.enabled) {
                tr.node.classList.add('--enabled');
                tr.node.classList.remove('--disabled');
            } else {
                tr.node.classList.remove('--enabled');
                tr.node.classList.add('--disabled');
            }
        }
    }
    changeActiveTriggerCounter(addend) {
        this.activeTriggerCounter += addend;
        if (this.counterNode) {
            this.counterNode.textContent = this.activeTriggerCounter;
            if (this.activeTriggerCounter > 0) {
                this.clearAllNode.classList.add('--active');
                this.clearAllNode.classList.remove('--disabled');
            } else {
                this.clearAllNode.classList.remove('--active');
                this.clearAllNode.classList.add('--disabled');
            }
        }
    }
    clearAll() {
        for (let trId in this.triggers) {
            if (this.triggers[trId].active) {
                this.toggleTrigger(this.triggers[trId]);
            }
        }
        for (let trId in this.triggers) {
            this.setTriggerPotential(this.triggers[trId]);
        }
        for (let cat in this.state) {
            this.setCategoryParam(cat);
        }
        this.filterItems();
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
        if (this.currentLang) {
            for (let key in this.currentLang) {
                let x = this.currentLang[key];
                if (x.source.toLowerCase() == trans || (x.target && x.target.toLowerCase() == trans)) {
                    k = key;
                }
            }
        }
        return k;
    }
    mount() {
        this.changeActiveTriggerCounter(0);
        if (this.clearAllNode) {
            this.clearAllNode.addEventListener('click', event => {
                event.stopPropagation();
                this.clearAll();
            });
        }
        this.filterItems();
        return this
    }
}

// HTML Structure
//  #{id}[data-filter]
//      [data-filter-clear-all]
//      [data-filter-counter]
//      [data-filter-trigger={filterCategory}:{value}]
//      ..
//  [data-filter-list-of={id}]
//      [data-filter-item-of={id}]
//          script[data-filter-item-data]
//              {"{filterCategory}":"{value}"}
//      ..
// Example HTML
/*<div data-filter id="filterId">
    <button data-filter-clear-all>Clear</button>
    <span data-filter-counter>0</span>

    <header>Animal</header>
    <button data-filter-trigger="animal:cat">Cat</button>
    <button data-filter-trigger="animal:dog">Dog</button>

    <header>Clothing</header>
    <button data-filter-trigger="clothing:shirt">Shirt</button>
    <button data-filter-trigger="clothing:pants">Pants</button>
</div>
<ul data-filter-list-of="filterId">
    <li data-filter-item-of="filterId">
        <script data-filter-item-data
                type="application/json">{"animal":"cat","clothing":"shirt"}</script>
        Cat with shirt
    </li>
    <li data-filter-item-of="filterId">
        <script data-filter-item-data
                type="application/json">{"animal":"dog","clothing":"shirt,pants"}</script>
        Dog with pants and shirt
    </li>
</ul>*/
