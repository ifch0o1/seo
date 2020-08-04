# SEO tracktor

## API Documentation

The API explantation and usages.

### Authentication (API Keys)
Всеки рекуест трябва да садържа `api_token` който е с дължина 32. <br>
Пример: `GET SITE/api/v1/keywords {api_token: XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX, ...}`
<b> Параметри на рекуеста </b> <br>

#### Keywords
`GET /api/v1/keywords`

<table>
    <thead>
        <tr>
            <th>Параметър</th>
            <th>Тип</th>
            <th>Описание</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td>include_poor</td>
            <td><code>(bool)</code> - Default <code>false</code> </td>
            <td>Връща всички думи включително и тези които не са одобрени</td>
        </tr>
    </tbody>
</table>

#### Posts
`GET /api/v1/aida_posts`


<table>
    <thead>
        <tr>
            <th>Параметър</th>
            <th>Тип</th>
            <th>Описание</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td>include_poor</td>
            <td><code>(bool)</code> - Default <code>false</code> </td>
            <td>Връща всички изречения включително и тези които не са одобрени</td>
        </tr>
    </tbody>
</table>
