<?php

namespace App\Http\Controllers\Topic;

use App\Http\Controllers\Controller;
use App\Http\Requests\Topic\TopicRequest;

use App\Models\Topic;
use App\Models\Expertise;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TopicController extends Controller
{

    // UC-13: Tìm kiếm đề tài
    // GET /topics?keyword=&page=&per_page=
    public function index(Request $request)
    {
        $keyword = trim($request->query('keyword', ''));
        $perPage = (int) $request->query('per_page', 10);

        $query = Topic::query();

        // tìm theo keyword
        if ($keyword) {
            $query->where('title', 'like', "%$keyword%");
        }

        $topics = $query->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $topics->items(),
            'meta' => [
                'total' => $topics->total(),
                'page' => $topics->currentPage(),
                'per_page' => $topics->perPage(),
                'last_page' => $topics->lastPage()
            ]
        ]);
    }

    // UC-14: Thêm đề tài
    // POST /topics
    public function store(TopicRequest $request): JsonResponse
    {
        $data = $request->validated();

        $topic = Topic::create($data);

        return response()->json([
            'success' => true,
            'message' => 'Thêm đề tài thành công',
            'data' => $topic
        ], 201);
    }

    // UC-15: Sửa đề tài
    // PUT /topics/{id}
    public function update(TopicRequest $request, $id): JsonResponse
    {
        $data = $request->validated();

        $topic = Topic::findOrFail($id);

        $topic->update($data);

        return response()->json([
            'success' => true,
            'message' => 'Cập nhật đề tài thành công',
            'data' => $topic
        ]);
    }

    // UC-16: Xóa đề tài
    // DELETE /topics/{id}
    public function destroy(string $id)
    {
        $topic = Topic::findOrFail($id);

        $topic->delete();

        return response()->json([
            'success' => true,
            'message' => 'Xóa đề tài thành công'
        ]);
    }
}
