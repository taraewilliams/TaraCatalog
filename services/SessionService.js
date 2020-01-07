app.service('Session', function ($cookies)
{
    var session_id = 'hpSessionId';
    var session_token = 'hpSessionToken';
    var session_user_id = 'hpSessionUserId';

    this.create = function(id, token, userID) {
        this.destroy();

        this.id = id;
        this.token = token;
        this.userID = userID;

        $cookies.put(session_id, this.id);
        $cookies.put(session_token, this.token);
        $cookies.put(session_user_id, this.userID);
    };

    this.destroy = function() {
        this.id = null;
        this.token = null;
        this.userID = null;

        $cookies.put(session_id, null);
        $cookies.put(session_token, null);
        $cookies.put(session_user_id, null);

        $cookies.remove(session_id);
        $cookies.remove(session_token);
        $cookies.remove(session_user_id);
    };

    try {
        this.id = $cookies.get(session_id) || null;
        this.token = $cookies.get(session_token) || null;
        this.userID = $cookies.get(session_user_id) || null;
    } catch (e) {
        console.log("Error creating session:", e);
        console.log("Destroying session.");
        this.destroy();
    }

    return this;
});
