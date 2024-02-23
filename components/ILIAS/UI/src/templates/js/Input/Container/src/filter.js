import filter from './filter.main';
import ContainerFactory from './container.factory';

il = il || {};
il.UI = il.UI || {};
il.UI.filter = filter($);

il.UI.Input = il.UI.Input || {};
il.UI.Input.Container = new ContainerFactory();
