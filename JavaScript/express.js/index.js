// This is an example of authentication with node js & express (including body-parser) & node-fetch.
// Some packages provide more possibilities so please make sure to use them if you need more functionality.
// This example uses the OAuth 2.0 Authorization Code Flow with PKCE (Proof Key for Code Exchange) to authenticate users with FACEIT.


// Packages and setup
const express = require('express')
const bodyParser = require('body-parser')
const dotenv = require("dotenv")

dotenv.config();
const fetch = (...args) => import('node-fetch').then(({ default: fetch }) => fetch(...args))
const app = express()

app.use(bodyParser.json())

let port = process.env.PORT || 3000

// This is code_challenge. It's strongly recommended to use SHA256 (with code_challenge_method:"S256") approach but we use Plain text in this example
// You can use the methods available in crypto.js file, like so:
/*
    const code_verified = base64_url_encode(process.env.CODE_VERIFIED)
    const code_challenge = sha256(code_verified)
*/
let code_challenge = process.env.CODE_VERIFIED

// Global Parameters. You can save it in another place.
// Please visit https://developers.faceit.com/apps in order to obtain this parameters and set up callback url (https://youurdomainhere/route/to/auth/callback)
let params={
    client_id: process.env.CLIENT_ID,
    client_secret: process.env.CLIENT_SECRET,
}

app.get("/auth", (req, res) => {
    // Query parameters for the request
    let CodeRequestParams = new URLSearchParams({
        client_id: params.client_id,
        response_type: 'code',
        code_challenge: code_challenge,
        redirect_popup: true,
        code_challenge_method: 'plain'
    })

    // Redirect
    res.redirect(`https://accounts.faceit.com?${CodeRequestParams.toString()}`)
})


// This callback path MUST match the one you set in https://developers.faceit.com/apps
app.get("/auth/callback", async(req, res) => {
    // Body for the POST
    let CodeExchangeParams = new URLSearchParams(Object.assign({
        code: req.query.code,
        grant_type: "authorization_code",
        code_verifier: process.env.CODE_VERIFIED // Highly encourage to use the SHA256 approach or at least base64_url_encode (both available in crypto.js file)
    }))

    try {
        const response = await fetch('https://api.faceit.com/auth/v1/oauth/token', {
            method: "POST",
            mode: "cors",
            headers: {
                "Accept": 'application/json',
                "Content-Type": 'application/x-www-form-urlencoded;charset=UTF-8',
                "Authorization": `Basic ${Buffer.from(`${params.client_id}:${params.client_secret}`).toString('base64')}`
            },
            body: CodeExchangeParams
        })

        const rawData = await response.text()

        try {
            // this is your data. Please make sure to store it in the secure place.
            // You'll need to use the refresh token to get a new access token (grant_type:"refresh_token")
            let data = JSON.parse(rawData)

            // JSON response
            res.setHeader("Content-Type", "application/json")
            res.status(200)
            res.json({
                "message": "success",
                "data": data
            })
        } catch (err) {
            res.status(412).send(err)
            console.log(err)
        }
    } catch (error) {
        console.log(error)
        res.status(503).send(error)
    }
})

//Launch
app.listen(port, () => console.log(`API listening on port ${port}`))
