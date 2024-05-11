'use strict';

document.addEventListener('DOMContentLoaded', () => {
	let tenders_toader = new TendersLoader(
		'tender-list',
		'pagination'
	);
	tenders_toader.init();
});

class TendersLoader {
	constructor(tender_list_css_class, pagination_css_class) {
		this.root = document.querySelector(`.${tender_list_css_class}`);

		if (null === this.root) {
			console.log('tenders loader is not initialized');
			return;
		}

		this.tender_list_css_class = tender_list_css_class;
		this.pagination_css_class  = pagination_css_class;

		this.root.addEventListener('click', ({target: t}) => {
			if (!t.closest(`.${this.tender_list_css_class}__try-again`)) {
				return;
			}

			this,this.tryAgain();
		})
	}

	getLoadingElem() {
		let elem = this.root.querySelector(`.${this.tender_list_css_class}__loading`);

		if (null === elem) {
			elem = document.createElement('div');
			elem.className = `${this.tender_list_css_class}__loading`;
			elem.innerHTML = 'Please wait...';
		}

		return elem;
	}

	getErrorElem(inner_html) {
		let elem = this.root.querySelector(`.${this.tender_list_css_class}__loading-error`);

		if (null === elem) {
			elem = document.createElement('div');
			elem.className = `${this.tender_list_css_class}__loading-error`;
			elem.innerHTML = inner_html;
		}

		return elem;
	}

	getListItem() {
		return this.root.querySelector(`.${this.tender_list_css_class}__items`);
	}

	tryAgain() {
		if (null === this.pagination.getActiveLinkElem()) {
			return;
		}

		let page_number = this.pagination.getActiveLinkElem().dataset.pageNumber;
		this.pagination.trigger(page_number);
	}

	init() {
		if (null === this.root) {
			console.log('tenders loader is not initialized');
			return;
		}

		let tender_templater = new TenderTemplater(
			'/admin-panel/get-tender-template',
			'/admin-panel/get-tenders-data'
		);
		this.pagination = new Pagination(
			`.${this.pagination_css_class}`,
			'pagination__nav-link',
			'pagination__nav-link_active'
		);

		tender_templater.initTenderTemplate();

		this.pagination.onTogglePage(async (page_number, query_string) => {
			if (null === this.getListItem()) {
				return;
			}

			this.getListItem().innerHTML = '';
			this.getListItem().prepend(this.getLoadingElem());

			let tenders_data = await tender_templater.getTendersData(page_number, query_string);
			this.getErrorElem().remove();
			this.getLoadingElem().remove();

			if (null != tenders_data['items'] && tenders_data['items'].length > 0) {
				console.dir('loaded');
				this.getListItem().innerHTML = tender_templater.buildTenderList(tenders_data['items']);
			} else {
				console.dir('loading error');
				let error_message = tenders_data['error'];

				if (null != error_message) {
					this.getListItem().prepend(this.getErrorElem(
						`${error_message}` +
						`<button class="${this.tender_list_css_class}__try-again">Try again</button>`
					));
				} else {
					this.getListItem().prepend(this.getErrorElem(
						'unknown error has occurred, please reload the page'
					));
				}
			}
		});
	}
}


class TenderTemplater {
	static template_url = '';
	static data_url = '';
	template = '';

	constructor(template_url, data_url) {
		this.template_url = template_url;
		this.data_url     = data_url;
	}

	async getTendersData(page_number, query_string) {
		return await fetch(`${this.data_url}${query_string}`)
			.then(response => response.json())
			.catch(response => []);

		return [{ publication_number: '463876-2013' }];
	}

	async initTenderTemplate() {
		this.template = await fetch(`${this.template_url}`)
			.then(response => response.text());
		// this.template = '<div>%publication_number%</div>';
	}

	fillTenderTemplate(tender_fields) {
		let template = this.template;

		for (let key in tender_fields) {
			let value = tender_fields[key];

			if (null === value) {
				value = '';
			}

			template = template.replace(`%${key.toUpperCase()}%`, value);
		}

		return template;
	}

	buildTenderList(tenders_data = []) {
		let tender_list = '';

		for (let i = 0; i < tenders_data.length; i++) {
			const tender_fields = tenders_data[i];

			tender_list += this.fillTenderTemplate(tender_fields);
		}

		return tender_list;
	}

	async generateTenderList(page_number, query_string) {
		await this.initTenderTemplate();

		let tenders_data = await this.getTendersData(page_number, query_string);
		let tender_list = this.buildTenderList(tenders_data);

		return tender_list;
	}
}

class Pagination {
	handlers = [];

	constructor(root_css_selector, link_css_class, active_css_class) {
		this.root = document.querySelector(root_css_selector);
		this.link_css_class   = `${link_css_class}`;
		this.active_css_class = `${active_css_class}`;

		if (null === this.root) {
			console.log("pagination is not initialized");
			return;
		}

		this.initLinks();

		this.root.addEventListener('click', (e) => {
			if (!e.target.closest(`.${link_css_class}`)) {
				return;
			}

			e.preventDefault();
			this.togglePage(e.target.dataset.pageNumber);
		});
	}

	initLinks() {
		let links = this.root.querySelectorAll(`.${this.link_css_class}`);

		for (let i = 0; i < links.length; i++) {
			const link = links[i];

			link.dataset.pageNumber = link.innerText.trim();
		}
	}

	getLinkElem(page_number = 1) {
		return this.root.querySelector(`[data-page-number="${page_number}"]`);
	}

	getActiveLinkElem() {
		return this.root.querySelector(`.${this.active_css_class}`);
	}

	togglePage(page_number) {
		if (this.getActiveLinkElem() === this.getLinkElem(page_number)) {
			return;
		}

		if (null !== this.getActiveLinkElem()) {
			this.getActiveLinkElem().classList.remove(this.active_css_class);
		}

		if (null !== this.getLinkElem(page_number)) {
			this.getLinkElem(page_number).classList.add(this.active_css_class);
		}

		this.trigger(page_number);
	}

	onTogglePage(handler) {
		this.handlers.push(handler);
	}

	offTogglePage() {
		this.handlers = [];
	}

	trigger(page_number) {
		for (let i = 0; i < this.handlers.length; i++) {
			let url = (new URL(this.getActiveLinkElem().href, location.origin));

			this.handlers[i](page_number, url.search);
		}
	}
}
