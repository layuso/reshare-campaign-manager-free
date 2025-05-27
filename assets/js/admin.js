const { useState, useEffect } = wp.element;
const { Button, Panel, PanelBody, Spinner, SelectControl, TextControl, Notice } = wp.components;

// Campaign Dashboard Component
const CampaignDashboard = () => {
    const [campaigns, setCampaigns] = useState([]);
    const [loading, setLoading] = useState(true);
    const [error, setError] = useState(null);

    useEffect(() => {
        loadCampaigns();
    }, []);

    const loadCampaigns = async () => {
        try {
            const response = await wp.apiFetch({
                path: '/wp/v2/reshare/campaigns',
            });
            setCampaigns(response);
            setLoading(false);
        } catch (err) {
            setError(err.message);
            setLoading(false);
        }
    };

    const updateCampaignStatus = async (campaignId, newStatus) => {
        try {
            await wp.apiFetch({
                path: `/wp/v2/reshare/campaigns/${campaignId}/status`,
                method: 'POST',
                data: { status: newStatus }
            });
            loadCampaigns();
        } catch (err) {
            setError(err.message);
        }
    };

    if (loading) return <Spinner />;
    if (error) return <Notice status="error">{error}</Notice>;

    return (
        <div className="reshare-campaign-table">
            <table className="wp-list-table widefat fixed striped">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Status</th>
                        <th>Posts</th>
                        <th>Frequency</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    {campaigns.map(campaign => (
                        <tr key={campaign.id}>
                            <td>{campaign.name}</td>
                            <td>
                                <span className={`status-tag status-${campaign.status}`}>
                                    {campaign.status}
                                </span>
                            </td>
                            <td>{campaign.post_count}</td>
                            <td>{`Every ${campaign.frequency} ${campaign.frequency_unit}`}</td>
                            <td>
                                {campaign.status === 'active' && (
                                    <Button
                                        isSecondary
                                        onClick={() => updateCampaignStatus(campaign.id, 'paused')}
                                    >
                                        Pause
                                    </Button>
                                )}
                                {campaign.status === 'paused' && (
                                    <Button
                                        isPrimary
                                        onClick={() => updateCampaignStatus(campaign.id, 'active')}
                                    >
                                        Resume
                                    </Button>
                                )}
                                <Button
                                    isDestructive
                                    onClick={() => updateCampaignStatus(campaign.id, 'cancelled')}
                                >
                                    Cancel
                                </Button>
                            </td>
                        </tr>
                    ))}
                </tbody>
            </table>
        </div>
    );
};

// Campaign Wizard Component
const CampaignWizard = ({ onClose }) => {
    const [step, setStep] = useState(1);
    const [campaign, setCampaign] = useState({
        name: '',
        posts: [],
        social_accounts: [],
        frequency: 24,
        frequency_unit: 'hours'
    });
    const [loading, setLoading] = useState(false);
    const [error, setError] = useState(null);

    const handleSubmit = async () => {
        setLoading(true);
        try {
            await wp.apiFetch({
                path: '/wp/v2/reshare/campaigns',
                method: 'POST',
                data: campaign
            });
            onClose();
        } catch (err) {
            setError(err.message);
        }
        setLoading(false);
    };

    const renderStep = () => {
        switch (step) {
            case 1:
                return (
                    <PostSelector
                        selectedPosts={campaign.posts}
                        onChange={posts => setCampaign({ ...campaign, posts })}
                    />
                );
            case 2:
                return (
                    <SocialAccountSelector
                        selectedAccounts={campaign.social_accounts}
                        onChange={accounts => setCampaign({ ...campaign, social_accounts: accounts })}
                    />
                );
            case 3:
                return (
                    <FrequencySelector
                        frequency={campaign.frequency}
                        frequencyUnit={campaign.frequency_unit}
                        onChange={(frequency, unit) => setCampaign({
                            ...campaign,
                            frequency,
                            frequency_unit: unit
                        })}
                    />
                );
            case 4:
                return (
                    <ReviewStep
                        campaign={campaign}
                        onChange={updates => setCampaign({ ...campaign, ...updates })}
                    />
                );
        }
    };

    return (
        <div className="reshare-campaign-wizard">
            <Panel>
                <PanelBody>
                    <h2>Create New Campaign</h2>
                    {error && <Notice status="error">{error}</Notice>}
                    {renderStep()}
                    <div className="step-buttons">
                        {step > 1 && (
                            <Button
                                isSecondary
                                onClick={() => setStep(step - 1)}
                            >
                                Previous
                            </Button>
                        )}
                        {step < 4 ? (
                            <Button
                                isPrimary
                                onClick={() => setStep(step + 1)}
                            >
                                Next
                            </Button>
                        ) : (
                            <Button
                                isPrimary
                                onClick={handleSubmit}
                                isBusy={loading}
                            >
                                Create Campaign
                            </Button>
                        )}
                    </div>
                </PanelBody>
            </Panel>
        </div>
    );
};

// Initialize the React app
document.addEventListener('DOMContentLoaded', function() {
    const container = document.getElementById('reshare-admin-app');
    if (container && wp.element && wp.element.render) {
        wp.element.render(
            <div>
                <CampaignDashboard />
            </div>,
            container
        );
    }

    // Handle new campaign button
    const newCampaignButton = document.getElementById('reshare-new-campaign');
    if (newCampaignButton) {
        newCampaignButton.addEventListener('click', function(e) {
            e.preventDefault();
            const modal = document.createElement('div');
            modal.className = 'reshare-modal-wrapper';
            document.body.appendChild(modal);
            
            wp.element.render(
                <div>
                    <div className="reshare-modal-backdrop" />
                    <div className="reshare-modal">
                        <CampaignWizard
                            onClose={() => {
                                document.body.removeChild(modal);
                                window.location.reload();
                            }}
                        />
                    </div>
                </div>,
                modal
            );
        });
    }
}); 