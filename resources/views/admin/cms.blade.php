@extends('layouts.admin')
@section('header_title', 'Content Management System (Blog, FAQs, Testimonials & Email Templates)')
@section('content')
<div class="row g-4 mb-4">
    <!-- Blog Manager -->
    <div class="col-lg-6">
        <div class="card-modern p-4 h-100">
            <h6 class="fw-bold mb-3">Publish Blog Post</h6>
            <form action="{{ route('admin.cms.blog.store') }}" method="POST" class="mb-4">
                @csrf
                <div class="row g-2 mb-2">
                    <div class="col-8"><input type="text" name="title" required placeholder="Article Title" class="form-control form-control-sm"></div>
                    <div class="col-4">
                        <select name="blog_category_id" class="form-select form-select-sm">
                            @foreach($blogCategories as $bc)
                                <option value="{{ $bc->id }}">{{ $bc->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <input type="text" name="excerpt" required placeholder="Short SEO excerpt..." class="form-control form-control-sm mb-2">
                <textarea name="content" rows="3" required placeholder="HTML or text article body..." class="form-control form-control-sm mb-2"></textarea>
                <button class="btn btn-sm btn-primary">Publish Article</button>
            </form>

            <h6 class="fw-bold small text-uppercase text-muted">Published Posts ({{ $blogPosts->count() }})</h6>
            <ul class="list-group list-group-flush small">
                @foreach($blogPosts as $post)
                    <li class="list-group-item px-0 d-flex justify-content-between align-items-center">
                        <span>{{ $post->title }}</span>
                        <form action="{{ route('admin.cms.blog.delete', $post) }}" method="POST">
                            @csrf @method('DELETE')
                            <button class="btn btn-sm btn-link text-danger p-0">Delete</button>
                        </form>
                    </li>
                @endforeach
            </ul>
        </div>
    </div>

    <!-- FAQs & Testimonials -->
    <div class="col-lg-6">
        <div class="card-modern p-4 mb-4">
            <h6 class="fw-bold mb-3">Add Frequently Asked Question</h6>
            <form action="{{ route('admin.cms.faq.store') }}" method="POST" class="row g-2">
                @csrf
                <div class="col-4">
                    <select name="category" class="form-select form-select-sm">
                        <option value="General">General</option>
                        <option value="Billing">Billing</option>
                        <option value="Security">Security</option>
                        <option value="Tools">Tools</option>
                    </select>
                </div>
                <div class="col-8"><input type="text" name="question" required placeholder="Question..." class="form-control form-control-sm"></div>
                <div class="col-9"><input type="text" name="answer" required placeholder="Detailed answer..." class="form-control form-control-sm"></div>
                <div class="col-3"><button class="btn btn-sm btn-dark w-100">Add FAQ</button></div>
            </form>
        </div>

        <div class="card-modern p-4">
            <h6 class="fw-bold mb-3">Add Customer Testimonial</h6>
            <form action="{{ route('admin.cms.testimonial.store') }}" method="POST" class="row g-2">
                @csrf
                <div class="col-4"><input type="text" name="author_name" required placeholder="Full Name" class="form-control form-control-sm"></div>
                <div class="col-4"><input type="text" name="author_role" required placeholder="Role" class="form-control form-control-sm"></div>
                <div class="col-4"><input type="text" name="company" required placeholder="Company" class="form-control form-control-sm"></div>
                <div class="col-9"><input type="text" name="quote" required placeholder="Customer quote..." class="form-control form-control-sm"></div>
                <div class="col-3"><button class="btn btn-sm btn-primary w-100">Save</button></div>
            </form>
        </div>
    </div>
</div>

<!-- Editable Email Templates -->
<div class="card-modern p-4">
    <h6 class="fw-bold mb-3">Automated Email Notification Templates ({{ $emailTemplates->count() }} Events)</h6>
    <div class="row g-3">
        @foreach($emailTemplates as $tpl)
            <div class="col-md-6">
                <form action="{{ route('admin.cms.email.update', $tpl) }}" method="POST" class="border rounded-3 p-3">
                    @csrf @method('PUT')
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <strong class="small">{{ $tpl->name }}</strong>
                        <code>{{ $tpl->key }}</code>
                    </div>
                    <input type="text" name="subject" value="{{ $tpl->subject }}" class="form-control form-control-sm mb-2">
                    <textarea name="body_html" rows="2" class="form-control form-control-sm mb-2">{{ $tpl->body_html }}</textarea>
                    <button class="btn btn-sm btn-outline-primary">Update Template</button>
                </form>
            </div>
        @endforeach
    </div>
</div>
@endsection

