import u from 'umbrellajs';

u.prototype.show = function ( display = 'block' ) {
	return this.each( function ( node: HTMLElement ) {
		node.style.display = display;
	} );
};

u.prototype.hide = function () {
	return this.each( function ( node: HTMLElement ) {
		node.style.display = 'none';
	} );
};

export default u;
