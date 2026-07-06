import { ConfigProvider } from '@app/components/config-provider';

export interface configProps {
	id: string;
}

const SearchNode = ( { config }: { config: configProps | null } ) => {
	return (
		<ConfigProvider value={ config }>
			<div className="fibosearch-bar">
				<form className="fibosearch-bar__form" role="search" action="/" method="get">
					<div className="fibosearch-bar__inner">
						<label className="fibosearch-bar__label">
							<input
								type="search"
								className="fibosearch-bar__input"
								name="s"
								placeholder="Search for products"
								autoComplete="off"
							/>
						</label>

						<button type="submit" className="fibosearch-bar__submit" aria-label="Search">
							Search
						</button>

						<input type="hidden" name="post_type" value="product" />
						<input type="hidden" name="fs" value="1" />
					</div>
				</form>
			</div>
		</ConfigProvider>
	);
};

export default SearchNode;
