import express from 'express'

const app = express()
app.use(express.json())
app.use(express.static('public'))

const VIBE_API = 'https://vibecode.bitrix24.tech'
const VIBE_APP_KEY = process.env.VIBE_APP_KEY

function vibeHeaders(bearer) {
  return {
    'Authorization': `Bearer ${bearer}`,
    'X-Api-Key': VIBE_APP_KEY,
    'Content-Type': 'application/json',
  }
}

// Get current user identity from session
app.get('/api/me', async (req, res) => {
  const bearer = req.headers['x-vibe-authorization']
  if (!bearer) return res.status(401).json({ error: 'No session' })

  const r = await fetch(`${VIBE_API}/v1/users/me`, { headers: vibeHeaders(bearer) })
  const data = await r.json()
  res.json(data)
})

// List active tasks for the current user
app.get('/api/tasks', async (req, res) => {
  const bearer = req.headers['x-vibe-authorization']
  if (!bearer) return res.status(401).json({ error: 'No session' })

  // First get current user id
  const meRes = await fetch(`${VIBE_API}/v1/me`, { headers: vibeHeaders(bearer) })
  const meData = await meRes.json()
  const userId = meData.data?.currentUser?.bitrixUserId
  if (!userId) return res.status(401).json({ error: 'Cannot identify user' })

  // Fetch active tasks (not completed/declined/deferred)
  const tasksRes = await fetch(
    `${VIBE_API}/v1/tasks?filter[RESPONSIBLE_ID]=${userId}&filter[!STATUS]=5&filter[!STATUS]=7&limit=100&select=id,title,status,priority,deadline,description,createdBy,groupId`,
    { headers: vibeHeaders(bearer) }
  )
  const tasksData = await tasksRes.json()
  res.json(tasksData)
})

// Update task (status, priority, deadline)
app.patch('/api/tasks/:id', async (req, res) => {
  const bearer = req.headers['x-vibe-authorization']
  if (!bearer) return res.status(401).json({ error: 'No session' })

  const r = await fetch(`${VIBE_API}/v1/tasks/${req.params.id}`, {
    method: 'PATCH',
    headers: vibeHeaders(bearer),
    body: JSON.stringify(req.body),
  })
  res.json(await r.json())
})

// Complete a task
app.post('/api/tasks/:id/complete', async (req, res) => {
  const bearer = req.headers['x-vibe-authorization']
  if (!bearer) return res.status(401).json({ error: 'No session' })

  const r = await fetch(`${VIBE_API}/v1/tasks/${req.params.id}`, {
    method: 'PATCH',
    headers: vibeHeaders(bearer),
    body: JSON.stringify({ status: 5 }),
  })
  res.json(await r.json())
})

// Start a task
app.post('/api/tasks/:id/start', async (req, res) => {
  const bearer = req.headers['x-vibe-authorization']
  if (!bearer) return res.status(401).json({ error: 'No session' })

  const r = await fetch(`${VIBE_API}/v1/tasks/${req.params.id}`, {
    method: 'PATCH',
    headers: vibeHeaders(bearer),
    body: JSON.stringify({ status: 3 }),
  })
  res.json(await r.json())
})

// Add comment to task
app.post('/api/tasks/:id/comments', async (req, res) => {
  const bearer = req.headers['x-vibe-authorization']
  if (!bearer) return res.status(401).json({ error: 'No session' })

  const r = await fetch(`${VIBE_API}/v1/tasks/${req.params.id}/comments`, {
    method: 'POST',
    headers: vibeHeaders(bearer),
    body: JSON.stringify({ text: req.body.text }),
  })
  res.json(await r.json())
})

// Get task comments
app.get('/api/tasks/:id/comments', async (req, res) => {
  const bearer = req.headers['x-vibe-authorization']
  if (!bearer) return res.status(401).json({ error: 'No session' })

  const r = await fetch(`${VIBE_API}/v1/tasks/${req.params.id}/comments`, {
    headers: vibeHeaders(bearer),
  })
  res.json(await r.json())
})

app.listen(3000, () => console.log('Tasks app running on port 3000'))
