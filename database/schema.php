<?php
/**
 * Returns CREATE TABLE statements. The {{AUTO}} / {{JSON}} tokens are replaced
 * with driver-specific SQL so the same schema runs on MySQL and SQLite.
 */

return [
    'users' => "CREATE TABLE IF NOT EXISTS users (
        id {{AUTO}},
        name VARCHAR(120) NOT NULL,
        email VARCHAR(190) NOT NULL UNIQUE,
        password VARCHAR(255) NOT NULL,
        role VARCHAR(30) NOT NULL DEFAULT 'fan',
        reset_token VARCHAR(120) NULL,
        reset_sent_at DATETIME NULL,
        created_at DATETIME NULL
    )",

    'players' => "CREATE TABLE IF NOT EXISTS players (
        id {{AUTO}},
        slug VARCHAR(160) NOT NULL UNIQUE,
        name VARCHAR(160) NOT NULL,
        number INT DEFAULT 0,
        position VARCHAR(80),
        position_short VARCHAR(12),
        age INT,
        nationality VARCHAR(80),
        nationality_code VARCHAR(8),
        foot VARCHAR(12),
        height INT,
        weight INT,
        club VARCHAR(160),
        availability VARCHAR(40),
        level VARCHAR(40),
        rating INT DEFAULT 0,
        fit INT DEFAULT 0,
        video INT DEFAULT 0,
        market_value VARCHAR(40),
        bio TEXT,
        photo VARCHAR(255),
        scout_user_id INT NULL,
        target_club VARCHAR(160) NULL,
        target_league VARCHAR(120) NULL,
        next_move_preference VARCHAR(30) NULL,
        next_move_notes VARCHAR(200) NULL,
        data {{JSON}},
        created_at DATETIME NULL
    )",

    'player_target_clubs' => "CREATE TABLE IF NOT EXISTS player_target_clubs (
        id {{AUTO}},
        player_id INT NOT NULL,
        club_name VARCHAR(160) NOT NULL,
        club_slug VARCHAR(160) NULL,
        type VARCHAR(20) NOT NULL DEFAULT 'possible',
        sort_order INT NOT NULL DEFAULT 0,
        note VARCHAR(200) NULL,
        created_at DATETIME NULL
    )",

    'trainer_profiles' => "CREATE TABLE IF NOT EXISTS trainer_profiles (
        id {{AUTO}},
        user_id INT NOT NULL UNIQUE,
        coaching_license VARCHAR(120) NULL,
        specialties {{JSON}} NULL,
        years_experience INT NULL,
        current_club VARCHAR(160) NULL,
        updated_at DATETIME NULL,
        created_at DATETIME NULL
    )",

    'trainer_target_clubs' => "CREATE TABLE IF NOT EXISTS trainer_target_clubs (
        id {{AUTO}},
        trainer_id INT NOT NULL,
        club_name VARCHAR(160) NOT NULL,
        club_slug VARCHAR(160) NULL,
        type VARCHAR(20) NOT NULL DEFAULT 'favourite',
        sort_order INT NOT NULL DEFAULT 0,
        note VARCHAR(200) NULL,
        created_at DATETIME NULL
    )",

    'clubs' => "CREATE TABLE IF NOT EXISTS clubs (
        id {{AUTO}},
        slug VARCHAR(160) NOT NULL UNIQUE,
        name VARCHAR(160) NOT NULL,
        country VARCHAR(80),
        country_code VARCHAR(8),
        league VARCHAR(120),
        founded INT,
        level VARCHAR(40),
        about TEXT,
        budget VARCHAR(40),
        data {{JSON}},
        created_at DATETIME NULL
    )",

    'news' => "CREATE TABLE IF NOT EXISTS news (
        id {{AUTO}},
        slug VARCHAR(190) NOT NULL UNIQUE,
        category VARCHAR(30),
        title VARCHAR(255),
        excerpt TEXT,
        body TEXT,
        date DATE,
        author VARCHAR(120),
        tag VARCHAR(60),
        source VARCHAR(120) NULL,
        source_url VARCHAR(500) NULL,
        image VARCHAR(500) NULL,
        external TINYINT NOT NULL DEFAULT 0,
        published_at DATETIME NULL,
        youtube_id VARCHAR(20) NULL,
        created_at DATETIME NULL,
        updated_at DATETIME NULL
    )",

    'highlights' => "CREATE TABLE IF NOT EXISTS highlights (
        id {{AUTO}},
        player_slug VARCHAR(160),
        caption VARCHAR(255),
        duration VARCHAR(12),
        views INT DEFAULT 0,
        video VARCHAR(255) NULL,
        created_at DATETIME NULL
    )",

    'recommendations' => "CREATE TABLE IF NOT EXISTS recommendations (
        id {{AUTO}},
        player_slug VARCHAR(160),
        fit INT DEFAULT 0,
        need VARCHAR(120),
        availability VARCHAR(40),
        confidence VARCHAR(40),
        reasons {{JSON}},
        created_at DATETIME NULL
    )",

    'fitpass_plans' => "CREATE TABLE IF NOT EXISTS fitpass_plans (
        id {{AUTO}},
        name VARCHAR(60),
        price VARCHAR(20),
        period VARCHAR(20),
        tier VARCHAR(30),
        popular INT DEFAULT 0,
        tagline VARCHAR(160),
        features {{JSON}},
        amount_monthly INT DEFAULT 0,
        amount_yearly INT DEFAULT 0,
        currency VARCHAR(8) DEFAULT 'EUR',
        created_at DATETIME NULL
    )",

    'subscriptions' => "CREATE TABLE IF NOT EXISTS subscriptions (
        id {{AUTO}},
        user_id INT NOT NULL,
        tier VARCHAR(30) NOT NULL,
        plan_name VARCHAR(60),
        billing_cycle VARCHAR(20) NOT NULL DEFAULT 'monthly',
        amount_cents INT NOT NULL DEFAULT 0,
        currency VARCHAR(8) DEFAULT 'EUR',
        status VARCHAR(30) NOT NULL DEFAULT 'active',
        payment_method VARCHAR(40),
        provider VARCHAR(30),
        provider_id VARCHAR(120),
        current_period_start DATETIME NULL,
        current_period_end DATETIME NULL,
        cancel_at DATETIME NULL,
        created_at DATETIME NULL
    )",

    'payments' => "CREATE TABLE IF NOT EXISTS payments (
        id {{AUTO}},
        user_id INT NOT NULL,
        subscription_id INT NULL,
        tier VARCHAR(30),
        amount_cents INT NOT NULL,
        tax_cents INT DEFAULT 0,
        total_cents INT NOT NULL,
        currency VARCHAR(8) DEFAULT 'EUR',
        billing_cycle VARCHAR(20),
        payment_method VARCHAR(40),
        provider VARCHAR(30),
        provider_payment_id VARCHAR(120),
        status VARCHAR(30) NOT NULL DEFAULT 'pending',
        meta {{JSON}},
        created_at DATETIME NULL
    )",

    'gateway_payments' => "CREATE TABLE IF NOT EXISTS gateway_payments (
        id {{AUTO}},
        user_id INT NOT NULL,
        payable_type VARCHAR(40) NOT NULL,
        payable_id INT NOT NULL,
        gateway VARCHAR(20) NOT NULL,
        gateway_payment_id VARCHAR(190) NULL,
        amount_cents INT NOT NULL,
        currency VARCHAR(8) DEFAULT 'EUR',
        status VARCHAR(30) NOT NULL DEFAULT 'pending',
        metadata {{JSON}},
        created_at DATETIME NULL
    )",

    'contact_messages' => "CREATE TABLE IF NOT EXISTS contact_messages (
        id {{AUTO}},
        name VARCHAR(120),
        email VARCHAR(190),
        message TEXT,
        created_at DATETIME NULL
    )",

    /* ---- Member social layer ---- */
    'member_profiles' => "CREATE TABLE IF NOT EXISTS member_profiles (
        id {{AUTO}},
        user_id INT NOT NULL UNIQUE,
        username VARCHAR(60) NOT NULL UNIQUE,
        headline VARCHAR(160) NULL,
        bio TEXT NULL,
        avatar VARCHAR(255) NULL,
        location VARCHAR(120) NULL,
        website VARCHAR(255) NULL,
        social {{JSON}} NULL,
        updated_at DATETIME NULL,
        created_at DATETIME NULL
    )",

    'posts' => "CREATE TABLE IF NOT EXISTS posts (
        id {{AUTO}},
        user_id INT NOT NULL,
        body TEXT NULL,
        media_type VARCHAR(12) DEFAULT 'none',
        media_path VARCHAR(255) NULL,
        video_url VARCHAR(500) NULL,
        created_at DATETIME NULL
    )",

    'feed_shares' => "CREATE TABLE IF NOT EXISTS feed_shares (
        id {{AUTO}},
        user_id INT NOT NULL,
        share_type VARCHAR(20) NOT NULL,
        target_id INT NULL,
        target_url VARCHAR(500) NULL,
        title VARCHAR(255) NULL,
        excerpt TEXT NULL,
        thumbnail VARCHAR(500) NULL,
        comment TEXT NULL,
        created_at DATETIME NULL
    )",

    'messages' => "CREATE TABLE IF NOT EXISTS messages (
        id {{AUTO}},
        from_user_id INT NOT NULL,
        to_user_id INT NOT NULL,
        body TEXT NOT NULL,
        attachment_path VARCHAR(255) NULL,
        read_at DATETIME NULL,
        created_at DATETIME NULL
    )",

    /* ---- Sport providers (gyms, massage, physio, etc.) ---- */
    'providers' => "CREATE TABLE IF NOT EXISTS providers (
        id {{AUTO}},
        slug VARCHAR(160) NOT NULL UNIQUE,
        type VARCHAR(40) NOT NULL,
        name VARCHAR(160) NOT NULL,
        tagline VARCHAR(255) NULL,
        description TEXT NULL,
        address VARCHAR(255) NULL,
        city VARCHAR(80) NULL,
        country VARCHAR(80) NULL,
        country_code VARCHAR(8) NULL,
        phone VARCHAR(40) NULL,
        email VARCHAR(190) NULL,
        website VARCHAR(255) NULL,
        status VARCHAR(20) NOT NULL DEFAULT 'active',
        data {{JSON}} NULL,
        created_at DATETIME NULL
    )",

    'provider_users' => "CREATE TABLE IF NOT EXISTS provider_users (
        id {{AUTO}},
        provider_id INT NOT NULL,
        user_id INT NOT NULL,
        role VARCHAR(30) NOT NULL DEFAULT 'owner',
        created_at DATETIME NULL
    )",

    'provider_plans' => "CREATE TABLE IF NOT EXISTS provider_plans (
        id {{AUTO}},
        provider_id INT NOT NULL,
        slug VARCHAR(80) NOT NULL,
        name VARCHAR(120) NOT NULL,
        tagline VARCHAR(160) NULL,
        billing_interval VARCHAR(20) NOT NULL DEFAULT 'monthly',
        amount_cents INT NOT NULL DEFAULT 0,
        currency VARCHAR(8) DEFAULT 'EUR',
        visit_credits INT NULL,
        features {{JSON}} NULL,
        active INT DEFAULT 1,
        popular INT DEFAULT 0,
        created_at DATETIME NULL
    )",

    'provider_memberships' => "CREATE TABLE IF NOT EXISTS provider_memberships (
        id {{AUTO}},
        user_id INT NOT NULL,
        provider_id INT NOT NULL,
        provider_plan_id INT NOT NULL,
        status VARCHAR(30) NOT NULL DEFAULT 'active',
        billing_cycle VARCHAR(20) NOT NULL DEFAULT 'monthly',
        amount_cents INT NOT NULL DEFAULT 0,
        currency VARCHAR(8) DEFAULT 'EUR',
        visits_remaining INT NULL,
        payment_method VARCHAR(40) NULL,
        current_period_start DATETIME NULL,
        current_period_end DATETIME NULL,
        cancel_at DATETIME NULL,
        created_at DATETIME NULL
    )",

    /* ---- Prediction competitions (points-based, no real money) ---- */
    'competitions' => "CREATE TABLE IF NOT EXISTS competitions (
        id {{AUTO}},
        slug VARCHAR(160) NOT NULL UNIQUE,
        name VARCHAR(160) NOT NULL,
        description TEXT NULL,
        status VARCHAR(20) NOT NULL DEFAULT 'upcoming',
        starts_at DATETIME NULL,
        ends_at DATETIME NULL,
        created_at DATETIME NULL
    )",

    'fixtures' => "CREATE TABLE IF NOT EXISTS fixtures (
        id {{AUTO}},
        competition_id INT NOT NULL,
        home_team VARCHAR(120) NOT NULL,
        away_team VARCHAR(120) NOT NULL,
        kickoff_at DATETIME NOT NULL,
        status VARCHAR(20) NOT NULL DEFAULT 'scheduled',
        home_score INT NULL,
        away_score INT NULL,
        stage VARCHAR(80) NULL,
        competition_name VARCHAR(160) NULL,
        venue VARCHAR(160) NULL,
        minute INT NULL,
        home_team_logo VARCHAR(255) NULL,
        away_team_logo VARCHAR(255) NULL,
        events {{JSON}} NULL,
        ai_prediction {{JSON}} NULL,
        match_type VARCHAR(20) NOT NULL DEFAULT 'competition',
        created_at DATETIME NULL
    )",

    'predictions' => "CREATE TABLE IF NOT EXISTS predictions (
        id {{AUTO}},
        user_id INT NOT NULL,
        fixture_id INT NOT NULL,
        pick VARCHAR(10) NOT NULL,
        home_score INT NULL,
        away_score INT NULL,
        points_earned INT NULL,
        created_at DATETIME NULL,
        UNIQUE(user_id, fixture_id)
    )",

    'scout_shortlists' => "CREATE TABLE IF NOT EXISTS scout_shortlists (
        id {{AUTO}},
        user_id INT NOT NULL,
        player_slug VARCHAR(160) NOT NULL,
        note TEXT NULL,
        created_at DATETIME NULL,
        UNIQUE(user_id, player_slug)
    )",

    'scout_gdpr_consent' => "CREATE TABLE IF NOT EXISTS scout_gdpr_consent (
        id {{AUTO}},
        user_id INT NOT NULL UNIQUE,
        accepted_at DATETIME NOT NULL,
        ip_address VARCHAR(45) NULL,
        user_agent VARCHAR(255) NULL
    )",

    /* ---- Marketing CRM ---- */
    'crm_campaigns' => "CREATE TABLE IF NOT EXISTS crm_campaigns (
        id {{AUTO}},
        name VARCHAR(160) NOT NULL,
        type VARCHAR(30) NOT NULL DEFAULT 'email',
        status VARCHAR(30) NOT NULL DEFAULT 'draft',
        start_date DATE NULL,
        end_date DATE NULL,
        utm_source VARCHAR(80) NULL,
        utm_medium VARCHAR(80) NULL,
        utm_campaign VARCHAR(120) NULL,
        budget_cents INT NULL,
        notes TEXT NULL,
        created_at DATETIME NULL,
        updated_at DATETIME NULL
    )",

    'crm_contacts' => "CREATE TABLE IF NOT EXISTS crm_contacts (
        id {{AUTO}},
        name VARCHAR(120) NOT NULL,
        email VARCHAR(190) NULL,
        phone VARCHAR(40) NULL,
        source VARCHAR(60) NOT NULL DEFAULT 'website',
        status VARCHAR(30) NOT NULL DEFAULT 'new',
        notes TEXT NULL,
        assigned_to INT NULL,
        user_id INT NULL,
        campaign_id INT NULL,
        referral_code VARCHAR(40) NULL,
        tags {{JSON}} NULL,
        created_at DATETIME NULL,
        updated_at DATETIME NULL
    )",

    'crm_activities' => "CREATE TABLE IF NOT EXISTS crm_activities (
        id {{AUTO}},
        contact_id INT NOT NULL,
        user_id INT NULL,
        type VARCHAR(30) NOT NULL DEFAULT 'note',
        subject VARCHAR(160) NULL,
        body TEXT NULL,
        meta {{JSON}} NULL,
        created_at DATETIME NULL
    )",

    /* ---- Referral tracking ---- */
    'referral_codes' => "CREATE TABLE IF NOT EXISTS referral_codes (
        id {{AUTO}},
        user_id INT NOT NULL UNIQUE,
        code VARCHAR(40) NOT NULL UNIQUE,
        label VARCHAR(80) NULL,
        created_at DATETIME NULL
    )",

    'referrals' => "CREATE TABLE IF NOT EXISTS referrals (
        id {{AUTO}},
        referrer_user_id INT NOT NULL,
        referred_user_id INT NULL,
        referred_email VARCHAR(190) NULL,
        referral_code_id INT NOT NULL,
        campaign_id INT NULL,
        status VARCHAR(30) NOT NULL DEFAULT 'clicked',
        created_at DATETIME NULL,
        signed_up_at DATETIME NULL,
        activated_at DATETIME NULL
    )",

    'referral_rewards' => "CREATE TABLE IF NOT EXISTS referral_rewards (
        id {{AUTO}},
        referral_id INT NOT NULL,
        user_id INT NOT NULL,
        action VARCHAR(40) NOT NULL,
        points INT NOT NULL DEFAULT 0,
        created_at DATETIME NULL
    )",

    /* ---- AI player analysis (performance, health, injury risk) ---- */
    'player_health_records' => "CREATE TABLE IF NOT EXISTS player_health_records (
        id {{AUTO}},
        player_id INT NOT NULL,
        record_date DATE NOT NULL,
        status VARCHAR(20) NOT NULL DEFAULT 'fit',
        injury_type VARCHAR(80) NULL,
        body_part VARCHAR(60) NULL,
        severity VARCHAR(20) NULL,
        notes TEXT NULL,
        expected_return DATE NULL,
        created_at DATETIME NULL
    )",

    'player_performance_snapshots' => "CREATE TABLE IF NOT EXISTS player_performance_snapshots (
        id {{AUTO}},
        player_id INT NOT NULL,
        snapshot_date DATE NOT NULL,
        metrics {{JSON}} NOT NULL,
        created_at DATETIME NULL
    )",

    'ai_analyses' => "CREATE TABLE IF NOT EXISTS ai_analyses (
        id {{AUTO}},
        player_id INT NOT NULL,
        type VARCHAR(20) NOT NULL DEFAULT 'full',
        summary TEXT NULL,
        scores {{JSON}} NULL,
        risk_percent INT NULL,
        factors {{JSON}} NULL,
        generated_at DATETIME NULL,
        model_version VARCHAR(40) NOT NULL DEFAULT 'heuristic-v1'
    )",

    /* ---- Kooora-style sports hub (standings, teams, stats, TV) ---- */
    'teams' => "CREATE TABLE IF NOT EXISTS teams (
        id {{AUTO}},
        slug VARCHAR(160) NOT NULL UNIQUE,
        name VARCHAR(160) NOT NULL,
        short_name VARCHAR(40) NULL,
        country VARCHAR(80) NULL,
        stadium VARCHAR(160) NULL,
        league_slug VARCHAR(80) NULL,
        logo VARCHAR(255) NULL,
        data {{JSON}} NULL,
        created_at DATETIME NULL
    )",

    'standings' => "CREATE TABLE IF NOT EXISTS standings (
        id {{AUTO}},
        competition_slug VARCHAR(80) NOT NULL,
        team_id INT NULL,
        team_name VARCHAR(160) NOT NULL,
        split_type VARCHAR(12) NOT NULL DEFAULT 'overall',
        position INT NOT NULL DEFAULT 0,
        played INT NOT NULL DEFAULT 0,
        won INT NOT NULL DEFAULT 0,
        drawn INT NOT NULL DEFAULT 0,
        lost INT NOT NULL DEFAULT 0,
        goals_for INT NOT NULL DEFAULT 0,
        goals_against INT NOT NULL DEFAULT 0,
        goal_diff INT NOT NULL DEFAULT 0,
        points INT NOT NULL DEFAULT 0,
        form VARCHAR(20) NULL,
        group_name VARCHAR(40) NULL,
        created_at DATETIME NULL
    )",

    'scorers' => "CREATE TABLE IF NOT EXISTS scorers (
        id {{AUTO}},
        competition_slug VARCHAR(80) NOT NULL,
        player_name VARCHAR(160) NOT NULL,
        team_name VARCHAR(160) NOT NULL,
        goals INT NOT NULL DEFAULT 0,
        assists INT NOT NULL DEFAULT 0,
        matches INT NOT NULL DEFAULT 0,
        created_at DATETIME NULL
    )",

    'transfer_deals' => "CREATE TABLE IF NOT EXISTS transfer_deals (
        id {{AUTO}},
        deal_date DATE NOT NULL,
        player_name VARCHAR(160) NOT NULL,
        player_slug VARCHAR(160) NULL,
        from_club VARCHAR(160) NOT NULL,
        to_club VARCHAR(160) NOT NULL,
        fee VARCHAR(40) NULL,
        fee_cents INT NULL,
        status VARCHAR(30) NOT NULL DEFAULT 'Rumour',
        league VARCHAR(80) NULL,
        deal_type VARCHAR(30) NULL,
        created_at DATETIME NULL
    )",

    'rankings' => "CREATE TABLE IF NOT EXISTS rankings (
        id {{AUTO}},
        ranking_type VARCHAR(30) NOT NULL DEFAULT 'fifa',
        rank INT NOT NULL,
        name VARCHAR(160) NOT NULL,
        country_code VARCHAR(8) NULL,
        points INT NOT NULL DEFAULT 0,
        movement INT NOT NULL DEFAULT 0,
        created_at DATETIME NULL
    )",

    'tv_broadcasts' => "CREATE TABLE IF NOT EXISTS tv_broadcasts (
        id {{AUTO}},
        fixture_id INT NULL,
        match_label VARCHAR(255) NOT NULL,
        competition_name VARCHAR(160) NULL,
        channel VARCHAR(120) NOT NULL,
        kickoff_at DATETIME NOT NULL,
        sport VARCHAR(20) NOT NULL DEFAULT 'football',
        created_at DATETIME NULL
    )",

    /* ---- Multivendor marketplace ---- */
    'vendors' => "CREATE TABLE IF NOT EXISTS vendors (
        id {{AUTO}},
        user_id INT NOT NULL UNIQUE,
        shop_name VARCHAR(160) NOT NULL,
        slug VARCHAR(160) NOT NULL UNIQUE,
        description TEXT NULL,
        logo_url VARCHAR(255) NULL,
        status VARCHAR(20) NOT NULL DEFAULT 'pending',
        rating_avg DECIMAL(3,2) NOT NULL DEFAULT 0,
        created_at DATETIME NULL
    )",

    'marketplace_listings' => "CREATE TABLE IF NOT EXISTS marketplace_listings (
        id {{AUTO}},
        vendor_id INT NOT NULL,
        title VARCHAR(255) NOT NULL,
        slug VARCHAR(190) NOT NULL UNIQUE,
        description TEXT NULL,
        price INT NOT NULL DEFAULT 0,
        currency VARCHAR(8) NOT NULL DEFAULT 'EUR',
        condition VARCHAR(20) NOT NULL DEFAULT 'new',
        category VARCHAR(30) NOT NULL DEFAULT 'other',
        sport VARCHAR(30) NOT NULL DEFAULT 'general',
        images {{JSON}} NULL,
        stock_qty INT NOT NULL DEFAULT 1,
        status VARCHAR(20) NOT NULL DEFAULT 'draft',
        shipping_info TEXT NULL,
        location_city VARCHAR(80) NULL,
        location_country VARCHAR(80) NULL,
        featured INT NOT NULL DEFAULT 0,
        created_at DATETIME NULL,
        updated_at DATETIME NULL
    )",

    'marketplace_cart_items' => "CREATE TABLE IF NOT EXISTS marketplace_cart_items (
        id {{AUTO}},
        user_id INT NOT NULL,
        listing_id INT NOT NULL,
        qty INT NOT NULL DEFAULT 1,
        created_at DATETIME NULL,
        UNIQUE(user_id, listing_id)
    )",

    'marketplace_orders' => "CREATE TABLE IF NOT EXISTS marketplace_orders (
        id {{AUTO}},
        buyer_id INT NOT NULL,
        vendor_id INT NOT NULL,
        listing_id INT NOT NULL,
        qty INT NOT NULL DEFAULT 1,
        unit_price INT NOT NULL DEFAULT 0,
        total INT NOT NULL DEFAULT 0,
        currency VARCHAR(8) NOT NULL DEFAULT 'EUR',
        status VARCHAR(20) NOT NULL DEFAULT 'pending',
        payment_method VARCHAR(40) NULL,
        shipping_address {{JSON}} NULL,
        created_at DATETIME NULL
    )",

    'marketplace_reviews' => "CREATE TABLE IF NOT EXISTS marketplace_reviews (
        id {{AUTO}},
        order_id INT NOT NULL UNIQUE,
        buyer_id INT NOT NULL,
        vendor_id INT NOT NULL,
        listing_id INT NOT NULL,
        rating INT NOT NULL DEFAULT 5,
        comment TEXT NULL,
        created_at DATETIME NULL
    )",

    /* ---- Team fan chat groups ---- */
    'chat_groups' => "CREATE TABLE IF NOT EXISTS chat_groups (
        id {{AUTO}},
        slug VARCHAR(160) NOT NULL UNIQUE,
        name VARCHAR(160) NOT NULL,
        team_name VARCHAR(160) NULL,
        team_slug VARCHAR(160) NULL,
        type VARCHAR(30) NOT NULL DEFAULT 'team_fan',
        fixture_id INT NULL,
        description TEXT NULL,
        cover_color VARCHAR(20) NULL,
        member_count INT NOT NULL DEFAULT 0,
        created_by INT NULL,
        created_at DATETIME NULL
    )",

    'chat_group_members' => "CREATE TABLE IF NOT EXISTS chat_group_members (
        id {{AUTO}},
        group_id INT NOT NULL,
        user_id INT NOT NULL,
        role VARCHAR(20) NOT NULL DEFAULT 'member',
        joined_at DATETIME NULL,
        UNIQUE(group_id, user_id)
    )",

    'chat_group_messages' => "CREATE TABLE IF NOT EXISTS chat_group_messages (
        id {{AUTO}},
        group_id INT NOT NULL,
        user_id INT NOT NULL,
        body TEXT NOT NULL,
        topic_tag VARCHAR(20) NULL,
        reply_to_id INT NULL,
        created_at DATETIME NULL,
        edited_at DATETIME NULL
    )",

    'chat_group_topics' => "CREATE TABLE IF NOT EXISTS chat_group_topics (
        id {{AUTO}},
        group_id INT NOT NULL,
        slug VARCHAR(40) NOT NULL,
        label VARCHAR(80) NOT NULL,
        sort_order INT NOT NULL DEFAULT 0,
        created_at DATETIME NULL
    )",

    /* ---- Direct messages (1:1) ---- */
    'dm_conversations' => "CREATE TABLE IF NOT EXISTS dm_conversations (
        id {{AUTO}},
        user_a_id INT NOT NULL,
        user_b_id INT NOT NULL,
        updated_at DATETIME NULL,
        created_at DATETIME NULL,
        UNIQUE(user_a_id, user_b_id)
    )",

    'dm_messages' => "CREATE TABLE IF NOT EXISTS dm_messages (
        id {{AUTO}},
        conversation_id INT NOT NULL,
        sender_id INT NOT NULL,
        body TEXT NOT NULL,
        message_type VARCHAR(32) NOT NULL DEFAULT 'text',
        read_at DATETIME NULL,
        created_at DATETIME NULL
    )",

    /* ---- Fan pages & topics ---- */
    'fan_pages' => "CREATE TABLE IF NOT EXISTS fan_pages (
        id {{AUTO}},
        owner_user_id INT NOT NULL,
        slug VARCHAR(160) NOT NULL UNIQUE,
        name VARCHAR(160) NOT NULL,
        team_name VARCHAR(160) NOT NULL,
        team_slug VARCHAR(160) NOT NULL,
        description TEXT NULL,
        avatar_url VARCHAR(255) NULL,
        cover_color VARCHAR(20) NULL,
        chat_group_id INT NULL,
        member_count INT NOT NULL DEFAULT 0,
        status VARCHAR(20) NOT NULL DEFAULT 'active',
        created_at DATETIME NULL
    )",

    'fan_page_members' => "CREATE TABLE IF NOT EXISTS fan_page_members (
        id {{AUTO}},
        fan_page_id INT NOT NULL,
        user_id INT NOT NULL,
        joined_at DATETIME NULL,
        UNIQUE(fan_page_id, user_id)
    )",

    'fan_topics' => "CREATE TABLE IF NOT EXISTS fan_topics (
        id {{AUTO}},
        fan_page_id INT NOT NULL,
        author_id INT NOT NULL,
        title VARCHAR(255) NOT NULL,
        body TEXT NOT NULL,
        type VARCHAR(30) NOT NULL DEFAULT 'general',
        fixture_id INT NULL,
        pinned INT NOT NULL DEFAULT 0,
        reply_count INT NOT NULL DEFAULT 0,
        created_at DATETIME NULL
    )",

    'fan_topic_replies' => "CREATE TABLE IF NOT EXISTS fan_topic_replies (
        id {{AUTO}},
        topic_id INT NOT NULL,
        user_id INT NOT NULL,
        body TEXT NOT NULL,
        created_at DATETIME NULL
    )",

    /* ---- Book-a-Talk (per-minute player sessions) ---- */
    'booking_profiles' => "CREATE TABLE IF NOT EXISTS booking_profiles (
        id {{AUTO}},
        user_id INT NOT NULL UNIQUE,
        player_slug VARCHAR(160) NULL,
        price_per_minute INT NOT NULL DEFAULT 500,
        currency VARCHAR(8) NOT NULL DEFAULT 'EUR',
        min_minutes INT NOT NULL DEFAULT 5,
        max_minutes INT NOT NULL DEFAULT 60,
        bio_pitch TEXT NULL,
        is_active INT NOT NULL DEFAULT 0,
        is_verified INT NOT NULL DEFAULT 0,
        rating_avg REAL NOT NULL DEFAULT 0,
        review_count INT NOT NULL DEFAULT 0,
        created_at DATETIME NULL,
        updated_at DATETIME NULL
    )",

    'booking_availability' => "CREATE TABLE IF NOT EXISTS booking_availability (
        id {{AUTO}},
        user_id INT NOT NULL,
        starts_at DATETIME NOT NULL,
        ends_at DATETIME NOT NULL,
        is_booked INT NOT NULL DEFAULT 0,
        created_at DATETIME NULL
    )",

    'bookings' => "CREATE TABLE IF NOT EXISTS bookings (
        id {{AUTO}},
        fan_user_id INT NOT NULL,
        player_user_id INT NOT NULL,
        slot_start DATETIME NOT NULL,
        slot_end DATETIME NOT NULL,
        duration_min INT NOT NULL,
        price_per_min INT NOT NULL,
        total_price INT NOT NULL,
        currency VARCHAR(8) NOT NULL DEFAULT 'EUR',
        status VARCHAR(30) NOT NULL DEFAULT 'pending',
        payment_method VARCHAR(40) NULL,
        meeting_url VARCHAR(500) NULL,
        notes TEXT NULL,
        created_at DATETIME NULL
    )",

    'booking_reviews' => "CREATE TABLE IF NOT EXISTS booking_reviews (
        id {{AUTO}},
        booking_id INT NOT NULL UNIQUE,
        rating INT NOT NULL DEFAULT 5,
        comment TEXT NULL,
        created_at DATETIME NULL
    )",

    /* ---- Sportify Pro Coaches (training subscriptions + sessions) ---- */
    'creator_profiles' => "CREATE TABLE IF NOT EXISTS creator_profiles (
        id {{AUTO}},
        user_id INT NOT NULL UNIQUE,
        slug VARCHAR(80) NOT NULL UNIQUE,
        display_name VARCHAR(120) NOT NULL,
        bio TEXT NULL,
        cover_url VARCHAR(500) NULL,
        avatar_url VARCHAR(500) NULL,
        city VARCHAR(80) NULL,
        country VARCHAR(80) NULL,
        country_code VARCHAR(8) NULL,
        lat REAL NULL,
        lng REAL NULL,
        radius_km INT NOT NULL DEFAULT 25,
        categories {{JSON}} NULL,
        is_active INT NOT NULL DEFAULT 0,
        is_approved INT NOT NULL DEFAULT 0,
        is_featured INT NOT NULL DEFAULT 0,
        rating_avg REAL NOT NULL DEFAULT 0,
        review_count INT NOT NULL DEFAULT 0,
        subscriber_count INT NOT NULL DEFAULT 0,
        created_at DATETIME NULL,
        updated_at DATETIME NULL
    )",

    'creator_tiers' => "CREATE TABLE IF NOT EXISTS creator_tiers (
        id {{AUTO}},
        creator_id INT NOT NULL,
        name VARCHAR(80) NOT NULL,
        price_monthly INT NOT NULL DEFAULT 0,
        description TEXT NULL,
        perks {{JSON}} NULL,
        sort_order INT NOT NULL DEFAULT 0,
        is_active INT NOT NULL DEFAULT 1,
        created_at DATETIME NULL
    )",

    'creator_subscriptions' => "CREATE TABLE IF NOT EXISTS creator_subscriptions (
        id {{AUTO}},
        fan_user_id INT NOT NULL,
        creator_id INT NOT NULL,
        tier_id INT NOT NULL,
        status VARCHAR(30) NOT NULL DEFAULT 'active',
        starts_at DATETIME NULL,
        ends_at DATETIME NULL,
        price_paid INT NOT NULL DEFAULT 0,
        payment_method VARCHAR(40) NULL,
        created_at DATETIME NULL
    )",

    'creator_posts' => "CREATE TABLE IF NOT EXISTS creator_posts (
        id {{AUTO}},
        creator_id INT NOT NULL,
        title VARCHAR(255) NULL,
        body TEXT NULL,
        media_urls {{JSON}} NULL,
        visibility VARCHAR(30) NOT NULL DEFAULT 'subscribers_only',
        moderation_flag INT NOT NULL DEFAULT 0,
        is_hidden INT NOT NULL DEFAULT 0,
        created_at DATETIME NULL
    )",

    'session_offerings' => "CREATE TABLE IF NOT EXISTS session_offerings (
        id {{AUTO}},
        creator_id INT NOT NULL,
        type VARCHAR(20) NOT NULL DEFAULT 'online',
        title VARCHAR(160) NOT NULL,
        duration_min INT NOT NULL DEFAULT 60,
        price INT NOT NULL DEFAULT 0,
        description TEXT NULL,
        location_address VARCHAR(255) NULL,
        sort_order INT NOT NULL DEFAULT 0,
        is_active INT NOT NULL DEFAULT 1,
        created_at DATETIME NULL
    )",

    'session_bookings' => "CREATE TABLE IF NOT EXISTS session_bookings (
        id {{AUTO}},
        fan_id INT NOT NULL,
        creator_id INT NOT NULL,
        offering_id INT NOT NULL,
        slot_id INT NULL,
        scheduled_at DATETIME NOT NULL,
        type VARCHAR(20) NOT NULL DEFAULT 'online',
        status VARCHAR(30) NOT NULL DEFAULT 'pending',
        total_price INT NOT NULL DEFAULT 0,
        currency VARCHAR(8) NOT NULL DEFAULT 'EUR',
        meeting_url VARCHAR(500) NULL,
        location_notes TEXT NULL,
        payment_method VARCHAR(40) NULL,
        created_at DATETIME NULL
    )",

    /* ---- Sportify Transfer Bureau (amateur transfer marketplace) ---- */
    'transfer_listings' => "CREATE TABLE IF NOT EXISTS transfer_listings (
        id {{AUTO}},
        club_user_id INT NOT NULL,
        team_name VARCHAR(160) NOT NULL,
        team_level VARCHAR(40) NOT NULL DEFAULT '1_herren',
        gender VARCHAR(12) NOT NULL DEFAULT 'men',
        positions_needed {{JSON}} NULL,
        league_level VARCHAR(60) NULL,
        city VARCHAR(80) NULL,
        lat REAL NULL,
        lng REAL NULL,
        radius_km INT NOT NULL DEFAULT 25,
        description TEXT NULL,
        status VARCHAR(20) NOT NULL DEFAULT 'active',
        created_at DATETIME NULL,
        updated_at DATETIME NULL
    )",

    'transfer_seekers' => "CREATE TABLE IF NOT EXISTS transfer_seekers (
        id {{AUTO}},
        user_id INT NOT NULL UNIQUE,
        type VARCHAR(20) NOT NULL DEFAULT 'player',
        positions {{JSON}} NULL,
        league_level_preference VARCHAR(60) NULL,
        city VARCHAR(80) NULL,
        lat REAL NULL,
        lng REAL NULL,
        radius_km INT NOT NULL DEFAULT 25,
        availability VARCHAR(30) NOT NULL DEFAULT 'immediate',
        bio TEXT NULL,
        is_premium_highlight INT NOT NULL DEFAULT 0,
        status VARCHAR(20) NOT NULL DEFAULT 'seeking',
        created_at DATETIME NULL,
        updated_at DATETIME NULL
    )",

    'trial_requests' => "CREATE TABLE IF NOT EXISTS trial_requests (
        id {{AUTO}},
        from_user_id INT NOT NULL,
        to_user_id INT NOT NULL,
        listing_id INT NULL,
        proposed_date DATETIME NULL,
        location VARCHAR(255) NULL,
        message TEXT NULL,
        status VARCHAR(20) NOT NULL DEFAULT 'pending',
        created_at DATETIME NULL,
        updated_at DATETIME NULL
    )",

    'transfer_contacts' => "CREATE TABLE IF NOT EXISTS transfer_contacts (
        id {{AUTO}},
        from_user_id INT NOT NULL,
        to_user_id INT NOT NULL,
        created_at DATETIME NULL
    )",

    /* ---- KickOff Arcade mini-games ---- */
    'game_scores' => "CREATE TABLE IF NOT EXISTS game_scores (
        id {{AUTO}},
        user_id INT NOT NULL,
        game VARCHAR(40) NOT NULL,
        score INT NOT NULL DEFAULT 0,
        team_name VARCHAR(80) NULL,
        meta {{JSON}} NULL,
        created_at DATETIME NULL
    )",

    /* ---- Role-based FIT-Pass membership tiers ---- */
    'membership_plans' => "CREATE TABLE IF NOT EXISTS membership_plans (
        id {{AUTO}},
        role VARCHAR(20) NOT NULL,
        slug VARCHAR(60) NOT NULL UNIQUE,
        name VARCHAR(60) NOT NULL,
        tier_level INT NOT NULL DEFAULT 0,
        price_monthly INT NOT NULL DEFAULT 0,
        price_yearly INT NOT NULL DEFAULT 0,
        currency VARCHAR(8) NOT NULL DEFAULT 'EUR',
        features {{JSON}} NOT NULL,
        limits {{JSON}} NULL,
        stripe_price_id VARCHAR(120) NULL,
        paypal_plan_id VARCHAR(120) NULL,
        is_active INT NOT NULL DEFAULT 1,
        sort_order INT NOT NULL DEFAULT 0,
        created_at DATETIME NULL
    )",

    'user_memberships' => "CREATE TABLE IF NOT EXISTS user_memberships (
        id {{AUTO}},
        user_id INT NOT NULL,
        plan_id INT NOT NULL,
        status VARCHAR(20) NOT NULL DEFAULT 'active',
        starts_at DATETIME NULL,
        ends_at DATETIME NULL,
        gateway VARCHAR(30) NULL,
        gateway_subscription_id VARCHAR(120) NULL,
        billing_cycle VARCHAR(20) NOT NULL DEFAULT 'monthly',
        created_at DATETIME NULL,
        updated_at DATETIME NULL
    )",

    /* ---- Sportify Markt (Transfermarkt-style market intelligence) ---- */
    'player_market_values' => "CREATE TABLE IF NOT EXISTS player_market_values (
        id {{AUTO}},
        player_id INT NOT NULL,
        value_eur INT NOT NULL DEFAULT 0,
        recorded_at DATETIME NOT NULL,
        created_at DATETIME NULL
    )",

    'transfer_records' => "CREATE TABLE IF NOT EXISTS transfer_records (
        id {{AUTO}},
        player_id INT NOT NULL,
        from_club VARCHAR(160) NOT NULL,
        to_club VARCHAR(160) NOT NULL,
        fee_eur INT NULL,
        type VARCHAR(20) NOT NULL DEFAULT 'permanent',
        transfer_date DATE NOT NULL,
        season VARCHAR(12) NOT NULL,
        created_at DATETIME NULL
    )",

    'transfer_rumours' => "CREATE TABLE IF NOT EXISTS transfer_rumours (
        id {{AUTO}},
        player_name VARCHAR(160) NOT NULL,
        player_id INT NULL,
        from_club VARCHAR(160) NOT NULL,
        to_club VARCHAR(160) NOT NULL,
        probability_percent INT NOT NULL DEFAULT 50,
        source VARCHAR(120) NULL,
        status VARCHAR(30) NOT NULL DEFAULT 'active',
        reported_at DATETIME NOT NULL,
        created_at DATETIME NULL
    )",

    /* ---- Only Talents (talent shorts + subscriptions) ---- */
    'talent_creator_profiles' => "CREATE TABLE IF NOT EXISTS talent_creator_profiles (
        id {{AUTO}},
        player_id INT NULL UNIQUE,
        user_id INT NULL UNIQUE,
        slug VARCHAR(80) NOT NULL UNIQUE,
        bio TEXT NULL,
        subscription_price_cents INT NOT NULL DEFAULT 499,
        currency VARCHAR(8) NOT NULL DEFAULT 'EUR',
        subscriber_count INT NOT NULL DEFAULT 0,
        verified INT NOT NULL DEFAULT 0,
        is_active INT NOT NULL DEFAULT 1,
        created_at DATETIME NULL,
        updated_at DATETIME NULL
    )",

    'talent_shorts' => "CREATE TABLE IF NOT EXISTS talent_shorts (
        id {{AUTO}},
        player_id INT NULL,
        creator_profile_id INT NULL,
        title VARCHAR(255) NOT NULL,
        caption TEXT NULL,
        video_url VARCHAR(500) NULL,
        embed_id VARCHAR(80) NULL,
        source VARCHAR(30) NOT NULL DEFAULT 'youtube',
        thumbnail VARCHAR(500) NULL,
        likes INT NOT NULL DEFAULT 0,
        views INT NOT NULL DEFAULT 0,
        is_premium INT NOT NULL DEFAULT 0,
        is_featured INT NOT NULL DEFAULT 0,
        external_key VARCHAR(120) NULL,
        category_slug VARCHAR(80) NULL,
        tags VARCHAR(255) NULL,
        is_available INT NULL DEFAULT 1,
        created_at DATETIME NULL
    )",

    'talent_subscriptions' => "CREATE TABLE IF NOT EXISTS talent_subscriptions (
        id {{AUTO}},
        subscriber_user_id INT NOT NULL,
        talent_creator_id INT NOT NULL,
        price_cents INT NOT NULL DEFAULT 0,
        currency VARCHAR(8) NOT NULL DEFAULT 'EUR',
        status VARCHAR(30) NOT NULL DEFAULT 'active',
        payment_method VARCHAR(40) NULL,
        provider VARCHAR(30) NULL,
        provider_ref VARCHAR(190) NULL,
        starts_at DATETIME NULL,
        ends_at DATETIME NULL,
        created_at DATETIME NULL
    )",

    'talent_short_likes' => "CREATE TABLE IF NOT EXISTS talent_short_likes (
        id {{AUTO}},
        short_id INT NOT NULL,
        user_id INT NOT NULL,
        created_at DATETIME NULL,
        UNIQUE(short_id, user_id)
    )",

    'shorts_feed' => "CREATE TABLE IF NOT EXISTS shorts_feed (
        id {{AUTO}},
        external_key VARCHAR(120) NOT NULL,
        title VARCHAR(255) NOT NULL,
        caption VARCHAR(255) NULL,
        youtube_id VARCHAR(20) NULL,
        video_url VARCHAR(500) NULL,
        thumbnail VARCHAR(500) NULL,
        duration VARCHAR(12) NULL,
        source VARCHAR(120) NULL,
        player_name VARCHAR(120) NULL,
        player_slug VARCHAR(160) NULL,
        position VARCHAR(80) NULL,
        views INT NOT NULL DEFAULT 0,
        published_at DATETIME NULL,
        updated_at DATETIME NULL,
        UNIQUE(external_key)
    )",

    /* ---- OnlyTalents discovery platform ---- */
    'talent_categories' => "CREATE TABLE IF NOT EXISTS talent_categories (
        id {{AUTO}},
        slug VARCHAR(80) NOT NULL UNIQUE,
        parent_id INT NULL,
        name_en VARCHAR(120) NOT NULL,
        name_de VARCHAR(120) NULL,
        name_ar VARCHAR(120) NULL,
        sport_type VARCHAR(40) NULL,
        sort_order INT NOT NULL DEFAULT 0,
        is_active INT NOT NULL DEFAULT 1,
        created_at DATETIME NULL,
        updated_at DATETIME NULL
    )",

    'discovery_profiles' => "CREATE TABLE IF NOT EXISTS discovery_profiles (
        id {{AUTO}},
        source_type VARCHAR(20) NOT NULL,
        source_id INT NOT NULL,
        user_id INT NULL,
        talent_type VARCHAR(40) NOT NULL DEFAULT 'football_player',
        sport_type VARCHAR(40) NOT NULL DEFAULT 'football',
        online_status VARCHAR(20) NOT NULL DEFAULT 'offline',
        experience_level VARCHAR(30) NULL,
        gender VARCHAR(12) NULL,
        age INT NULL,
        country VARCHAR(80) NULL,
        city VARCHAR(80) NULL,
        region VARCHAR(80) NULL,
        languages {{JSON}} NULL,
        categories {{JSON}} NULL,
        skills {{JSON}} NULL,
        availability {{JSON}} NULL,
        verified INT NOT NULL DEFAULT 0,
        featured INT NOT NULL DEFAULT 0,
        has_videos INT NOT NULL DEFAULT 0,
        has_highlights INT NOT NULL DEFAULT 0,
        view_count INT NOT NULL DEFAULT 0,
        follower_count INT NOT NULL DEFAULT 0,
        metadata {{JSON}} NULL,
        last_active_at DATETIME NULL,
        created_at DATETIME NULL,
        updated_at DATETIME NULL,
        UNIQUE(source_type, source_id)
    )",

    /* ---- OnlyTalents paid communities & monetized content ---- */
    'ot_communities' => "CREATE TABLE IF NOT EXISTS ot_communities (
        id {{AUTO}},
        owner_user_id INT NOT NULL,
        slug VARCHAR(80) NOT NULL UNIQUE,
        name VARCHAR(160) NOT NULL,
        description TEXT NULL,
        cover_image VARCHAR(500) NULL,
        type VARCHAR(30) NOT NULL DEFAULT 'free',
        category_slug VARCHAR(80) NULL,
        sport_type VARCHAR(40) NULL,
        member_count INT NOT NULL DEFAULT 0,
        is_featured INT NOT NULL DEFAULT 0,
        status VARCHAR(20) NOT NULL DEFAULT 'draft',
        created_at DATETIME NULL,
        updated_at DATETIME NULL
    )",

    'ot_subscription_plans' => "CREATE TABLE IF NOT EXISTS ot_subscription_plans (
        id {{AUTO}},
        community_id INT NOT NULL,
        name VARCHAR(80) NOT NULL,
        price_cents INT NOT NULL DEFAULT 0,
        currency VARCHAR(8) NOT NULL DEFAULT 'EUR',
        billing_interval VARCHAR(20) NOT NULL DEFAULT 'month',
        stripe_price_id VARCHAR(120) NULL,
        sort_order INT NOT NULL DEFAULT 0,
        benefits {{JSON}} NULL,
        is_active INT NOT NULL DEFAULT 1,
        created_at DATETIME NULL
    )",

    'ot_community_members' => "CREATE TABLE IF NOT EXISTS ot_community_members (
        id {{AUTO}},
        community_id INT NOT NULL,
        user_id INT NOT NULL,
        plan_id INT NULL,
        status VARCHAR(20) NOT NULL DEFAULT 'active',
        role VARCHAR(20) NOT NULL DEFAULT 'member',
        joined_at DATETIME NULL,
        UNIQUE(community_id, user_id)
    )",

    'ot_courses' => "CREATE TABLE IF NOT EXISTS ot_courses (
        id {{AUTO}},
        community_id INT NOT NULL,
        owner_user_id INT NOT NULL,
        slug VARCHAR(80) NOT NULL,
        title VARCHAR(255) NOT NULL,
        description TEXT NULL,
        cover_image VARCHAR(500) NULL,
        access_level VARCHAR(20) NOT NULL DEFAULT 'paid',
        free_preview_lessons INT NULL,
        sort_order INT NOT NULL DEFAULT 0,
        published INT NOT NULL DEFAULT 0,
        created_at DATETIME NULL,
        updated_at DATETIME NULL,
        UNIQUE(community_id, slug)
    )",

    'ot_course_modules' => "CREATE TABLE IF NOT EXISTS ot_course_modules (
        id {{AUTO}},
        course_id INT NOT NULL,
        title VARCHAR(255) NOT NULL,
        access_level VARCHAR(20) NOT NULL DEFAULT 'paid',
        sort_order INT NOT NULL DEFAULT 0,
        created_at DATETIME NULL
    )",

    'ot_course_lessons' => "CREATE TABLE IF NOT EXISTS ot_course_lessons (
        id {{AUTO}},
        module_id INT NOT NULL,
        title VARCHAR(255) NOT NULL,
        content_type VARCHAR(20) NOT NULL DEFAULT 'video',
        access_level VARCHAR(20) NOT NULL DEFAULT 'paid',
        required_plan_id INT NULL,
        preview_seconds INT NULL,
        video_url VARCHAR(500) NULL,
        embed_url VARCHAR(500) NULL,
        file_path VARCHAR(500) NULL,
        body TEXT NULL,
        sort_order INT NOT NULL DEFAULT 0,
        duration_min INT NULL,
        created_at DATETIME NULL
    )",

    'ot_community_posts' => "CREATE TABLE IF NOT EXISTS ot_community_posts (
        id {{AUTO}},
        community_id INT NOT NULL,
        user_id INT NOT NULL,
        type VARCHAR(20) NOT NULL DEFAULT 'post',
        access_level VARCHAR(30) NOT NULL DEFAULT 'members',
        required_plan_id INT NULL,
        title VARCHAR(255) NULL,
        body TEXT NULL,
        pinned INT NOT NULL DEFAULT 0,
        like_count INT NOT NULL DEFAULT 0,
        created_at DATETIME NULL
    )",

    'ot_community_comments' => "CREATE TABLE IF NOT EXISTS ot_community_comments (
        id {{AUTO}},
        post_id INT NOT NULL,
        user_id INT NOT NULL,
        body TEXT NOT NULL,
        created_at DATETIME NULL
    )",

    'ot_live_sessions' => "CREATE TABLE IF NOT EXISTS ot_live_sessions (
        id {{AUTO}},
        community_id INT NOT NULL,
        host_user_id INT NOT NULL,
        title VARCHAR(255) NOT NULL,
        starts_at DATETIME NOT NULL,
        platform VARCHAR(30) NOT NULL DEFAULT 'zoom',
        meeting_url VARCHAR(500) NULL,
        created_at DATETIME NULL
    )",

    'ot_talent_content' => "CREATE TABLE IF NOT EXISTS ot_talent_content (
        id {{AUTO}},
        user_id INT NOT NULL,
        community_id INT NULL,
        type VARCHAR(20) NOT NULL DEFAULT 'video',
        title VARCHAR(255) NOT NULL,
        access_level VARCHAR(30) NOT NULL DEFAULT 'free',
        required_plan_id INT NULL,
        preview_seconds INT NULL,
        file_path VARCHAR(500) NULL,
        url VARCHAR(500) NULL,
        thumbnail VARCHAR(500) NULL,
        created_at DATETIME NULL
    )",

    'ot_community_post_likes' => "CREATE TABLE IF NOT EXISTS ot_community_post_likes (
        id {{AUTO}},
        post_id INT NOT NULL,
        user_id INT NOT NULL,
        created_at DATETIME NULL,
        UNIQUE(post_id, user_id)
    )",

    'ot_booking_requests' => "CREATE TABLE IF NOT EXISTS ot_booking_requests (
        id {{AUTO}},
        community_id INT NULL,
        requester_user_id INT NOT NULL,
        host_user_id INT NOT NULL,
        session_type VARCHAR(40) NOT NULL DEFAULT 'coaching',
        message TEXT NULL,
        preferred_at DATETIME NULL,
        status VARCHAR(20) NOT NULL DEFAULT 'pending',
        created_at DATETIME NULL
    )",

    'talent_imported_videos' => "CREATE TABLE IF NOT EXISTS talent_imported_videos (
        id {{AUTO}},
        user_id INT NULL,
        source_type VARCHAR(20) NOT NULL DEFAULT 'youtube',
        source_id INT NULL,
        youtube_id VARCHAR(32) NULL,
        title VARCHAR(255) NOT NULL,
        description TEXT NULL,
        thumbnail VARCHAR(500) NULL,
        video_url VARCHAR(500) NULL,
        published_at DATETIME NULL,
        imported_at DATETIME NULL,
        UNIQUE(source_type, source_id, youtube_id)
    )",

    /* ---- FIT-Pass AI Wellness Assistant (Phase 1) ---- */
    'wellness_user_profiles' => "CREATE TABLE IF NOT EXISTS wellness_user_profiles (
        id {{AUTO}},
        user_id INT NOT NULL UNIQUE,
        goals {{JSON}} NULL,
        preferred_sports {{JSON}} NULL,
        preferred_hours VARCHAR(40) NULL,
        location_lat DECIMAL(10,7) NULL,
        location_lng DECIMAL(10,7) NULL,
        location_city VARCHAR(80) NULL,
        onboarding_complete INT NOT NULL DEFAULT 0,
        created_at DATETIME NULL,
        updated_at DATETIME NULL
    )",

    'wellness_calendar_connections' => "CREATE TABLE IF NOT EXISTS wellness_calendar_connections (
        id {{AUTO}},
        user_id INT NOT NULL,
        provider VARCHAR(30) NOT NULL,
        account_email VARCHAR(190) NULL,
        tokens_encrypted TEXT NULL,
        sync_enabled INT NOT NULL DEFAULT 1,
        last_sync_at DATETIME NULL,
        created_at DATETIME NULL,
        updated_at DATETIME NULL,
        UNIQUE(user_id, provider)
    )",

    'wellness_recommendations' => "CREATE TABLE IF NOT EXISTS wellness_recommendations (
        id {{AUTO}},
        user_id INT NOT NULL,
        rec_key VARCHAR(80) NOT NULL,
        payload {{JSON}} NOT NULL,
        expires_at DATETIME NULL,
        created_at DATETIME NULL,
        UNIQUE(user_id, rec_key)
    )",

    'wellness_gdpr_consent' => "CREATE TABLE IF NOT EXISTS wellness_gdpr_consent (
        id {{AUTO}},
        user_id INT NOT NULL UNIQUE,
        consented INT NOT NULL DEFAULT 0,
        consent_version VARCHAR(20) NOT NULL DEFAULT '1.0',
        consented_at DATETIME NULL,
        ip_address VARCHAR(45) NULL,
        created_at DATETIME NULL
    )",

    /* ---- Potential partners CRM (media, clubs, leagues, ecosystem) ---- */
    'partner_leads' => "CREATE TABLE IF NOT EXISTS partner_leads (
        id {{AUTO}},
        name VARCHAR(190) NOT NULL,
        type VARCHAR(40) NOT NULL DEFAULT 'media',
        subtype VARCHAR(80) NULL,
        country VARCHAR(8) NULL,
        city VARCHAR(80) NULL,
        league VARCHAR(120) NULL,
        website VARCHAR(255) NULL,
        email VARCHAR(190) NULL,
        email_confidence VARCHAR(30) NOT NULL DEFAULT 'needs_research',
        source_url VARCHAR(500) NULL,
        invite_status VARCHAR(30) NOT NULL DEFAULT 'pending',
        invited_at DATETIME NULL,
        accepted_at DATETIME NULL,
        notes TEXT NULL,
        tags {{JSON}} NULL,
        created_at DATETIME NULL,
        updated_at DATETIME NULL
    )",

    'partner_invite_logs' => "CREATE TABLE IF NOT EXISTS partner_invite_logs (
        id {{AUTO}},
        partner_id INT NOT NULL,
        campaign VARCHAR(120) NOT NULL DEFAULT 'official_partner',
        from_address VARCHAR(190) NOT NULL,
        to_address VARCHAR(190) NOT NULL,
        subject VARCHAR(255) NULL,
        status VARCHAR(30) NOT NULL DEFAULT 'queued',
        error_message TEXT NULL,
        meta {{JSON}} NULL,
        created_at DATETIME NULL
    )",
];
