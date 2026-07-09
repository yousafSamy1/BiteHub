<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>BiteHub Executive Report</title>
    <style>
        body { font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; color: #2c3e50; margin: 0; padding: 0; }
        .page { padding: 40px; }
        .header { text-align: center; border-bottom: 3px solid #ff6b35; padding-bottom: 20px; margin-bottom: 30px; }
        .header h1 { margin: 0; color: #ff6b35; font-size: 32px; letter-spacing: 1px; }
        .header h2 { margin: 5px 0 15px 0; color: #34495e; font-size: 20px; font-weight: 300; }
        .header .date { font-size: 14px; color: #7f8c8d; font-weight: bold; background: #f8f9fa; padding: 5px 15px; border-radius: 15px; display: inline-block; }
        
        .section-title { font-size: 18px; color: #ffffff; background-color: #34495e; padding: 10px 15px; margin-top: 30px; margin-bottom: 15px; border-radius: 4px; }
        .section-title i { margin-right: 8px; }
        
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        th, td { padding: 12px 15px; text-align: left; }
        th { background-color: #f8f9fa; color: #2c3e50; font-weight: bold; font-size: 13px; text-transform: uppercase; border-bottom: 2px solid #bdc3c7; }
        td { font-size: 15px; border-bottom: 1px solid #ecf0f1; }
        tr:nth-child(even) td { background-color: #fdfdfd; }
        
        .metric-name { font-weight: 600; color: #34495e; width: 40%; }
        .value { font-family: 'Courier New', Courier, monospace; font-weight: bold; color: #2c3e50; }
        
        .trend-up { color: #27ae60; font-weight: bold; }
        .trend-down { color: #e74c3c; font-weight: bold; }
        .trend-flat { color: #95a5a6; font-weight: bold; }
        .trend-danger-up { color: #e74c3c; font-weight: bold; } /* For things like cancelled orders where UP is BAD */
        .trend-success-down { color: #27ae60; font-weight: bold; } /* Where DOWN is GOOD */
        
        .alert-box { background-color: #fcf8e3; border-left: 5px solid #f0ad4e; padding: 15px; margin-bottom: 30px; }
        .alert-box h4 { margin: 0 0 5px 0; color: #8a6d3b; font-size: 15px; }
        .alert-box p { margin: 0; color: #8a6d3b; font-size: 13px; }

        .footer { text-align: center; font-size: 11px; color: #95a5a6; margin-top: 50px; border-top: 1px solid #ecf0f1; padding-top: 20px; }
    </style>
</head>
<body>
    <div class="page">
        <div class="header">
            <h1>BITEHUB</h1>
            <h2>Executive Platform Statistics & Insights</h2>
            <div class="date">{{ $data['date'] }}</div>
        </div>

        @if($data['onboarding']['pending_kitchens'] > 0)
        <div class="alert-box">
            <h4>⚠️ Attention Required</h4>
            <p>There are currently <strong>{{ $data['onboarding']['pending_kitchens'] }}</strong> kitchen(s) awaiting your verification approval in the Admin Dashboard.</p>
        </div>
        @endif

        <!-- SECTION 1: FINANCIALS -->
        <div class="section-title">1. Financial Overview</div>
        <table>
            <thead>
                <tr>
                    <th>Metric</th>
                    <th>Today</th>
                    <th>Yesterday</th>
                    <th>Variance</th>
                    <th>Trend</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td class="metric-name">Gross Revenue (EGP)</td>
                    <td class="value">{{ number_format($data['financial']['revenue']['today'], 2) }}</td>
                    <td class="value">{{ number_format($data['financial']['revenue']['yesterday'], 2) }}</td>
                    <td class="value">{{ number_format($data['financial']['revenue']['diff'], 2) }}</td>
                    <td>
                        @if($data['financial']['revenue']['trend'] > 0) <span class="trend-up">▲ +{{ $data['financial']['revenue']['trend'] }}%</span>
                        @elseif($data['financial']['revenue']['trend'] < 0) <span class="trend-down">▼ {{ $data['financial']['revenue']['trend'] }}%</span>
                        @else <span class="trend-flat">▬ 0%</span> @endif
                    </td>
                </tr>
                <tr>
                    <td class="metric-name">Avg. Order Value (AOV)</td>
                    <td class="value">{{ number_format($data['financial']['aov']['today'], 2) }}</td>
                    <td class="value">{{ number_format($data['financial']['aov']['yesterday'], 2) }}</td>
                    <td class="value">{{ number_format($data['financial']['aov']['diff'], 2) }}</td>
                    <td>
                        @if($data['financial']['aov']['trend'] > 0) <span class="trend-up">▲ +{{ $data['financial']['aov']['trend'] }}%</span>
                        @elseif($data['financial']['aov']['trend'] < 0) <span class="trend-down">▼ {{ $data['financial']['aov']['trend'] }}%</span>
                        @else <span class="trend-flat">▬ 0%</span> @endif
                    </td>
                </tr>
                <tr>
                    <td class="metric-name">Lost Rev. (Cancelled Orders)</td>
                    <td class="value text-danger" style="color: #e74c3c;">{{ number_format($data['financial']['lost_revenue']['today'], 2) }}</td>
                    <td class="value">{{ number_format($data['financial']['lost_revenue']['yesterday'], 2) }}</td>
                    <td class="value">{{ number_format($data['financial']['lost_revenue']['diff'], 2) }}</td>
                    <td>
                        @if($data['financial']['lost_revenue']['trend'] > 0) <span class="trend-danger-up">▲ +{{ $data['financial']['lost_revenue']['trend'] }}%</span>
                        @elseif($data['financial']['lost_revenue']['trend'] < 0) <span class="trend-success-down">▼ {{ $data['financial']['lost_revenue']['trend'] }}%</span>
                        @else <span class="trend-flat">▬ 0%</span> @endif
                    </td>
                </tr>
            </tbody>
        </table>

        <!-- SECTION 2: OPERATIONS -->
        <div class="section-title">2. Operational Performance</div>
        <table>
            <thead>
                <tr>
                    <th>Metric</th>
                    <th>Today</th>
                    <th>Yesterday</th>
                    <th>Variance</th>
                    <th>Trend</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td class="metric-name">Total Orders (All statuses)</td>
                    <td class="value">{{ number_format($data['operational']['total_orders']['today']) }}</td>
                    <td class="value">{{ number_format($data['operational']['total_orders']['yesterday']) }}</td>
                    <td class="value">{{ number_format($data['operational']['total_orders']['diff']) }}</td>
                    <td>
                        @if($data['operational']['total_orders']['trend'] > 0) <span class="trend-up">▲ +{{ $data['operational']['total_orders']['trend'] }}%</span>
                        @elseif($data['operational']['total_orders']['trend'] < 0) <span class="trend-down">▼ {{ $data['operational']['total_orders']['trend'] }}%</span>
                        @else <span class="trend-flat">▬ 0%</span> @endif
                    </td>
                </tr>
                <tr>
                    <td class="metric-name">Cancelled Orders Count</td>
                    <td class="value" style="color: #e74c3c;">{{ number_format($data['operational']['cancelled_orders']['today']) }}</td>
                    <td class="value">{{ number_format($data['operational']['cancelled_orders']['yesterday']) }}</td>
                    <td class="value">{{ number_format($data['operational']['cancelled_orders']['diff']) }}</td>
                    <td>
                        @if($data['operational']['cancelled_orders']['trend'] > 0) <span class="trend-danger-up">▲ +{{ $data['operational']['cancelled_orders']['trend'] }}%</span>
                        @elseif($data['operational']['cancelled_orders']['trend'] < 0) <span class="trend-success-down">▼ {{ $data['operational']['cancelled_orders']['trend'] }}%</span>
                        @else <span class="trend-flat">▬ 0%</span> @endif
                    </td>
                </tr>
                <tr>
                    <td class="metric-name">New Catering Requests</td>
                    <td class="value">{{ number_format($data['operational']['catering_requests']['today']) }}</td>
                    <td class="value">{{ number_format($data['operational']['catering_requests']['yesterday']) }}</td>
                    <td class="value">{{ number_format($data['operational']['catering_requests']['diff']) }}</td>
                    <td>
                        @if($data['operational']['catering_requests']['trend'] > 0) <span class="trend-up">▲ +{{ $data['operational']['catering_requests']['trend'] }}%</span>
                        @elseif($data['operational']['catering_requests']['trend'] < 0) <span class="trend-down">▼ {{ $data['operational']['catering_requests']['trend'] }}%</span>
                        @else <span class="trend-flat">▬ 0%</span> @endif
                    </td>
                </tr>
                <tr>
                    <td class="metric-name">New Meal Subscriptions</td>
                    <td class="value">{{ number_format($data['operational']['new_subscriptions']['today']) }}</td>
                    <td class="value">{{ number_format($data['operational']['new_subscriptions']['yesterday']) }}</td>
                    <td class="value">{{ number_format($data['operational']['new_subscriptions']['diff']) }}</td>
                    <td>
                        @if($data['operational']['new_subscriptions']['trend'] > 0) <span class="trend-up">▲ +{{ $data['operational']['new_subscriptions']['trend'] }}%</span>
                        @elseif($data['operational']['new_subscriptions']['trend'] < 0) <span class="trend-down">▼ {{ $data['operational']['new_subscriptions']['trend'] }}%</span>
                        @else <span class="trend-flat">▬ 0%</span> @endif
                    </td>
                </tr>
            </tbody>
        </table>

        <!-- SECTION 3: NETWORK GROWTH -->
        <div class="section-title">3. Network Expansion & Onboarding</div>
        <table>
            <thead>
                <tr>
                    <th>Metric</th>
                    <th>Today</th>
                    <th>Yesterday</th>
                    <th>Variance</th>
                    <th>Trend</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td class="metric-name">New Customer Registrations</td>
                    <td class="value">{{ number_format($data['onboarding']['new_customers']['today']) }}</td>
                    <td class="value">{{ number_format($data['onboarding']['new_customers']['yesterday']) }}</td>
                    <td class="value">{{ number_format($data['onboarding']['new_customers']['diff']) }}</td>
                    <td>
                        @if($data['onboarding']['new_customers']['trend'] > 0) <span class="trend-up">▲ +{{ $data['onboarding']['new_customers']['trend'] }}%</span>
                        @elseif($data['onboarding']['new_customers']['trend'] < 0) <span class="trend-down">▼ {{ $data['onboarding']['new_customers']['trend'] }}%</span>
                        @else <span class="trend-flat">▬ 0%</span> @endif
                    </td>
                </tr>
                <tr>
                    <td class="metric-name">New Kitchens (Joined)</td>
                    <td class="value">{{ number_format($data['onboarding']['new_kitchens']['today']) }}</td>
                    <td class="value">{{ number_format($data['onboarding']['new_kitchens']['yesterday']) }}</td>
                    <td class="value">{{ number_format($data['onboarding']['new_kitchens']['diff']) }}</td>
                    <td>
                        @if($data['onboarding']['new_kitchens']['trend'] > 0) <span class="trend-up">▲ +{{ $data['onboarding']['new_kitchens']['trend'] }}%</span>
                        @elseif($data['onboarding']['new_kitchens']['trend'] < 0) <span class="trend-down">▼ {{ $data['onboarding']['new_kitchens']['trend'] }}%</span>
                        @else <span class="trend-flat">▬ 0%</span> @endif
                    </td>
                </tr>
                <tr>
                    <td class="metric-name">New Caterers (Joined)</td>
                    <td class="value">{{ number_format($data['onboarding']['new_caterers']['today']) }}</td>
                    <td class="value">{{ number_format($data['onboarding']['new_caterers']['yesterday']) }}</td>
                    <td class="value">{{ number_format($data['onboarding']['new_caterers']['diff']) }}</td>
                    <td>
                        @if($data['onboarding']['new_caterers']['trend'] > 0) <span class="trend-up">▲ +{{ $data['onboarding']['new_caterers']['trend'] }}%</span>
                        @elseif($data['onboarding']['new_caterers']['trend'] < 0) <span class="trend-down">▼ {{ $data['onboarding']['new_caterers']['trend'] }}%</span>
                        @else <span class="trend-flat">▬ 0%</span> @endif
                    </td>
                </tr>
                <tr>
                    <td class="metric-name">New Delivery Agents</td>
                    <td class="value">{{ number_format($data['onboarding']['new_agents']['today']) }}</td>
                    <td class="value">{{ number_format($data['onboarding']['new_agents']['yesterday']) }}</td>
                    <td class="value">{{ number_format($data['onboarding']['new_agents']['diff']) }}</td>
                    <td>
                        @if($data['onboarding']['new_agents']['trend'] > 0) <span class="trend-up">▲ +{{ $data['onboarding']['new_agents']['trend'] }}%</span>
                        @elseif($data['onboarding']['new_agents']['trend'] < 0) <span class="trend-down">▼ {{ $data['onboarding']['new_agents']['trend'] }}%</span>
                        @else <span class="trend-flat">▬ 0%</span> @endif
                    </td>
                </tr>
            </tbody>
        </table>

        <div class="footer">
            BiteHub System Data Architecture | Document ID: BH-{{ strtoupper(Str::random(8)) }} | Rendered: {{ \Carbon\Carbon::now()->format('Y-m-d H:i') }}
        </div>
    </div>
</body>
</html>
