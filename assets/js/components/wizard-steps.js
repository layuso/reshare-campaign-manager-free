const { useState, useEffect } = wp.element;
const { Button, TextControl, SelectControl, Spinner, Notice } = wp.components;

// Post Selector Component
export const PostSelector = ({ selectedPosts, onChange }) => {
    const [posts, setPosts] = useState([]);
    const [loading, setLoading] = useState(true);
    const [error, setError] = useState(null);
    const [search, setSearch] = useState('');

    useEffect(() => {
        loadPosts();
    }, [search]);

    const loadPosts = async () => {
        try {
            const response = await wp.apiFetch({
                path: `/wp/v2/posts?search=${search}&per_page=10`,
            });
            setPosts(response);
            setLoading(false);
        } catch (err) {
            setError(err.message);
            setLoading(false);
        }
    };

    if (loading) return <Spinner />;
    if (error) return <Notice status="error">{error}</Notice>;

    return (
        <div className="reshare-post-selector">
            <TextControl
                label="Search Posts"
                value={search}
                onChange={setSearch}
            />
            <div className="reshare-post-list">
                {posts.map(post => (
                    <div key={post.id} className="post-item">
                        <label>
                            <input
                                type="checkbox"
                                checked={selectedPosts.some(p => p.id === post.id)}
                                onChange={() => {
                                    const isSelected = selectedPosts.some(p => p.id === post.id);
                                    onChange(
                                        isSelected
                                            ? selectedPosts.filter(p => p.id !== post.id)
                                            : [...selectedPosts, { id: post.id, title: post.title.rendered }]
                                    );
                                }}
                            />
                            {post.title.rendered}
                        </label>
                    </div>
                ))}
            </div>
        </div>
    );
};

// Social Account Selector Component
export const SocialAccountSelector = ({ selectedAccounts, onChange }) => {
    const [accounts, setAccounts] = useState([]);
    const [loading, setLoading] = useState(true);
    const [error, setError] = useState(null);

    useEffect(() => {
        loadAccounts();
    }, []);

    const loadAccounts = async () => {
        try {
            const response = await wp.apiFetch({
                path: '/wp/v2/reshare/social-accounts',
            });
            setAccounts(response);
            setLoading(false);
        } catch (err) {
            setError(err.message);
            setLoading(false);
        }
    };

    if (loading) return <Spinner />;
    if (error) return <Notice status="error">{error}</Notice>;

    return (
        <div className="reshare-social-selector">
            {accounts.length === 0 ? (
                <Notice status="warning">
                    No social accounts connected. You can save as draft or proceed to 'Pending' state.
                </Notice>
            ) : (
                accounts.map(account => (
                    <div key={account.id} className="account-item">
                        <label>
                            <input
                                type="checkbox"
                                checked={selectedAccounts.includes(account.id)}
                                onChange={() => {
                                    const isSelected = selectedAccounts.includes(account.id);
                                    onChange(
                                        isSelected
                                            ? selectedAccounts.filter(id => id !== account.id)
                                            : [...selectedAccounts, account.id]
                                    );
                                }}
                            />
                            {account.name} ({account.type})
                        </label>
                    </div>
                ))
            )}
        </div>
    );
};

// Frequency Selector Component
export const FrequencySelector = ({ frequency, frequencyUnit, onChange }) => {
    return (
        <div className="reshare-frequency-selector">
            <div className="frequency-controls">
                <TextControl
                    type="number"
                    label="Frequency"
                    value={frequency}
                    onChange={value => onChange(parseInt(value), frequencyUnit)}
                    min={1}
                />
                <SelectControl
                    label="Unit"
                    value={frequencyUnit}
                    options={[
                        { label: 'Hours', value: 'hours' },
                        { label: 'Days', value: 'days' },
                    ]}
                    onChange={value => onChange(frequency, value)}
                />
            </div>
        </div>
    );
};

// Review Step Component
export const ReviewStep = ({ campaign, onChange }) => {
    return (
        <div className="reshare-review-step">
            <TextControl
                label="Campaign Name"
                value={campaign.name}
                onChange={name => onChange({ name })}
                required
            />
            <div className="review-section">
                <h3>Selected Posts ({campaign.posts.length})</h3>
                <ul>
                    {campaign.posts.map(post => (
                        <li key={post.id}>{post.title}</li>
                    ))}
                </ul>
            </div>
            <div className="review-section">
                <h3>Social Accounts ({campaign.social_accounts.length})</h3>
                <p>Selected accounts: {campaign.social_accounts.join(', ') || 'None'}</p>
            </div>
            <div className="review-section">
                <h3>Frequency</h3>
                <p>Every {campaign.frequency} {campaign.frequency_unit}</p>
            </div>
        </div>
    );
}; 