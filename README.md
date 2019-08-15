# laravel-filter
A simple trait which can be used to filter multiple columns with one query string. Also supports columns on related models and query scopes.

**How to use?**

* **Step 1:**
Place the Filter.php trait in your project, in this exampling I'm using a new folder App/Traits. 

* **Step 2:**
Add the trait to the model you wish to filter. Add it right after you declared the class, like this:
```
use App\Traits\Filter;

class Order extends Model {

  use Filter;
```


* **Step 3:** 
Add the filterable columns on your model. You can choose filterable (relationship) columns and scope for each controller function. In the example below you can see I added filterable columns for the controller's index() function. Inside this array you can pass the relation, null means that the columns don't belong to any relation but that they're owned by the model. The array key "scope" is used to add a scope to the filters as well allowing you to customize the filtering process even more. 

```
protected $filters = [
        'index' => [
            null => ['reference', 'printed'],
            'receiverAddress' => ['zipcode', 'number', 'city'],
            'receiverRelation' => ['name_1'],
            'senderRelation' => ['name_1'],
            'scope' => ['JvglNumber']
    ]
;
```

* **Step 4:**
You're all set! Now inside your controller you can use the filter like so:

```
$query = Order::where('deleted', 0);

if ($request->input('filter')) {
     $query->filter($request->input('filter'));
}

```

