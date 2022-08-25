import {$, $$} from '../General/Aliases';
import BurgerMenu from '../Classes/BurgerMenu';
import SmoothHashLinks from "../Classes/SmoothHashLinks";
import ScrollSensitive from '../Classes/ScrollSensitive';
import LinkTo from '../Classes/LinkTo';
import {Accordion, AccordionGroup, toggleEvent} from '../Classes/Accordion';
import Modal from '../Classes/Modal';
import smoothscroll from 'smoothscroll-polyfill';

new BurgerMenu();
new SmoothHashLinks(120);
smoothscroll.polyfill();
new LinkTo();
$$('[data-scroll-sensitive]').forEach(el => {
  new ScrollSensitive(el);
});
let accordionGroups = {};
$$('[data-acc-el="group"]').forEach(el => {
  accordionGroups[el.id] = new AccordionGroup(el);
});
let accordionSingles = {};
$$('[data-acc="single"]').forEach(el => {
  accordionSingles[el.id] = new Accordion(el);
});
let modals = {};
$$('[data-modal]').forEach(el => {
  modals[el.id] = new Modal(el);
});

$$('[data-to-top]').forEach(el => {
  el.on('click', function(){
    window.scrollTo({
      top: 0,
      left: 0,
      behavior: 'smooth'
    });
  });
});

$$('.u-initially-hidden').forEach(el => {
  el.classList.remove('u-initially-hidden');
});


$('body').classList.remove('u-no-transition');
