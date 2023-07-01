## SearchRepo Class Documentation

The SearchRepo class is a utility class designed to simplify searching and sorting data using Laravel's Eloquent ORM or Query Builder. It provides a convenient way to perform searches, paginate results, and add additional columns to the search output. This documentation will guide you through the usage and features of the SearchRepo class.

### Class Details

- Class Name: SearchRepo
- Author: Felix (https://github.com/felixkpt)
- License: MIT

### Importing the SearchRepo Class

To use the SearchRepo class, import it into your code as follows:

```php
use App\Repositories\SearchRepo;
```

### Instantiating the SearchRepo Class

To start using the SearchRepo class, you need to instantiate it by passing the query builder instance and other optional parameters. Here's an example:

```php
$searchRepo = SearchRepo::of($builder, $searchable = [], $sortable = []);
```

- `$builder` (required): The query builder instance representing the data source.
- `$searchable` (optional): An array of columns that can be searched. If not provided, it will be fetched from the model's `searchable` property if available.
- `$sortable` (optional): An array of columns that can be sorted.

### Searching Data

The SearchRepo class provides a simple way to perform search operations on the data. Follow these steps to perform a search:

1. Set the search term by accessing the `q` parameter in the request or by any other desired method.

2. Call the `paginate()` method to retrieve paginated search results:

   ```php
   $perPage = 10; // Set the number of items per page
   $results = $searchRepo->paginate($perPage);
   ```

3. Access the search results using the `data` property of the returned results:

   ```php
   $data = $results->data;
   ```

### Sorting Data

The SearchRepo class allows sorting of data based on specified columns. To enable sorting, provide the sortable columns as an array to the `of()` method:

```php
$sortableColumns = ['column1', 'column2'];
$searchRepo = SearchRepo::of($builder, $searchable, $sortableColumns);
```

To sort the data, include the `orderBy` and `orderDirection` query parameters in the request. The SearchRepo class will handle the sorting automatically.

### Adding Additional Columns

The SearchRepo class allows you to include additional columns in the search results. This is useful when you need to include calculated or derived values. Follow these steps to add additional columns:

1. Call the `addColumn()` method on the SearchRepo instance, passing the column name and a callback function as arguments:

   ```php
   $searchRepo->addColumn('new_column', function ($item) {
       // Calculate or derive the value for the new column
       return $item->column1 + $item->column2;
   });
   ```

2. The new column will be added to each item in the search results.

### Retrieving Raw Data

If you need to retrieve the raw data without pagination, you can use the `get()` method:

```php
$rawData = $searchRepo->get();
```

The raw data will be returned in the `data` property of the result.

### Example Usage with Post Model

Here's an example of how you can use the SearchRepo class with a `Post` model:

```php
use App\Repositories\SearchRepo;
use App\Models\Post;

// Create an instance of the SearchRepo for the Post model
$searchRepo = SearchRepo::of(new Post());

// Set the searchable columns for the Post model
$searchableColumns = ['

title', 'content'];

// Set the sortable columns for the Post model
$sortableColumns = ['id', 'title'];

// Set the searchable and sortable columns
$searchRepo->setSearchableColumns($searchableColumns)
           ->setSortableColumns($sortableColumns);

// Perform the search
$results = $searchRepo->search();

// Get the paginated results
$paginatedResults = $results->paginate(10);

// Get the search results without pagination
$unpaginatedResults = $results->get();

// Access the sortable columns
$sortable = $results->sortable;

// Loop through the paginated results
foreach ($paginatedResults as $post) {
    // Access the Post attributes
    $postId = $post->id;
    $title = $post->title;
    $content = $post->content;
    $image = $post->image;

    // Perform custom operations on the Post attributes
    // ...
}

// Loop through the unpaginated results
foreach ($unpaginatedResults['data'] as $post) {
    // Access the Post attributes
    $postId = $post->id;
    $title = $post->title;
    $content = $post->content;
    $image = $post->image;

    // Perform custom operations on the Post attributes
    // ...
}

// Access the sortable columns
$sortable = $unpaginatedResults['sortable'];

// Example of using the `get()` method
$postData = $searchRepo->get(['title', 'content']);

// Access the retrieved posts
$posts = $postData['data'];
$sortableColumns = $postData['sortable'];

// Loop through the retrieved posts
foreach ($posts as $post) {
    // Access the Post attributes
    $postId = $post->id;
    $title = $post->title;
    $content = $post->content;
    $image = $post->image;

    // Perform custom operations on the Post attributes
    // ...
}
```

Please note that this is just a sample documentation with an example usage of the SearchRepo class. You can adapt the code and instructions according to your specific application and database structure.

## License

The SearchRepo class is released under the MIT License. For more details, please refer to the [LICENSE](https://opensource.org/license/mit/) file.

---

This concludes the documentation for the SearchRepo class. If you have any further questions or need assistance, please don't hesitate to reach out. Good luck with your implementation!