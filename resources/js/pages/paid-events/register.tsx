import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import MainLayout from '@/layouts/main-layout';
import { Head, router, useForm } from '@inertiajs/react';
import axios from 'axios';
import { AlertCircle, ArrowLeft, ArrowRight, CheckCircle2, Loader2, ShoppingCart } from 'lucide-react';
import { FormEvent, useState } from 'react';

type PaidEvent = {
    id: number;
    title: string;
    slug: string;
    semester: string;
    registration_fee: number;
    student_id_rules: string | null;
    student_id_rules_guide: string | null;
    pickup_points: { name: string }[] | null;
    departments: { name: string }[] | null;
    sections: { name: string }[] | null;
    lab_teacher_names: { initial: string; full_name: string }[] | null;
    tshirt_sizes: string[] | null;
    tshirt_guideline_url: string | null;
};

type User = {
    email: string;
    name: string | null;
    student_id: string | null;
    phone: string | null;
    department: string | null;
    gender: string | null;
};

type TshirtSize = {
    value: string;
    label: string;
};

type Gender = {
    value: string;
    label: string;
};

type RegisterPageProps = {
    paidEvent: PaidEvent;
    user: User;
    tshirtSizes: TshirtSize[];
    genders: Gender[];
};

type FormData = {
    student_id: string;
    name: string;
    phone: string;
    department: string;
    section: string;
    gender: string;
    lab_teacher_name: string;
    tshirt_size: string;
    transport_service_required: boolean;
    pickup_point: string;
};

export default function RegisterPage({ paidEvent, user, tshirtSizes, genders }: RegisterPageProps) {
    const [step, setStep] = useState(1);
    const [isValidatingStudentId, setIsValidatingStudentId] = useState(false);
    const [studentIdError, setStudentIdError] = useState<string | null>(null);
    const [validationErrors, setValidationErrors] = useState<Record<string, string>>({});

    const { data, setData, post, processing, errors } = useForm<FormData>({
        student_id: user.student_id || '',
        name: user.name || '',
        phone: user.phone || '',
        department: user.department || '',
        section: '',
        gender: user.gender || '',
        lab_teacher_name: '',
        tshirt_size: '',
        transport_service_required: false,
        pickup_point: '',
    });

    const validateStudentIdStep = async () => {
        if (!data.student_id) {
            setStudentIdError('Student ID is required');
            return false;
        }

        setIsValidatingStudentId(true);
        setStudentIdError(null);

        try {
            const response = await axios.post(`/paid-events/${paidEvent.slug}/register/validate-student-id`, {
                student_id: data.student_id,
            });

            if (response.data.valid) {
                setIsValidatingStudentId(false);
                return true;
            } else {
                setStudentIdError(response.data.message);
                setIsValidatingStudentId(false);
                return false;
            }
        } catch (error: any) {
            if (error.response?.data?.message) {
                setStudentIdError(error.response.data.message);
            } else {
                setStudentIdError('An error occurred while validating your student ID. Please try again.');
            }
            setIsValidatingStudentId(false);
            return false;
        }
    };

    const handleNextStep = async (e?: React.MouseEvent<HTMLButtonElement>) => {
        if (e) {
            e.preventDefault();
            e.stopPropagation();
        }
        
        if (step === 1) {
            const isValid = await validateStudentIdStep();
            if (isValid) {
                setValidationErrors({});
                setStep(2);
            }
        } else if (step === 2) {
            // Validate personal details
            const errors: Record<string, string> = {};
            
            if (!data.name?.trim()) {
                errors.name = 'Full name is required';
            }
            if (!data.phone?.trim()) {
                errors.phone = 'Phone number is required';
            }
            if (!data.department?.trim()) {
                errors.department = 'Department is required';
            }
            if (!data.section?.trim()) {
                errors.section = 'Section is required';
            }
            if (!data.gender) {
                errors.gender = 'Gender is required';
            }
            if (!data.lab_teacher_name?.trim()) {
                errors.lab_teacher_name = 'Lab teacher name is required';
            }
            if (!data.tshirt_size) {
                errors.tshirt_size = 'T-shirt size is required';
            }

            if (Object.keys(errors).length > 0) {
                setValidationErrors(errors);
                return;
            }

            setValidationErrors({});
            setStep(3);
        } else if (step === 3) {
            // Validate transport
            const errors: Record<string, string> = {};
            
            if (data.transport_service_required && !data.pickup_point?.trim()) {
                errors.pickup_point = 'Please select a pickup point';
            }

            if (Object.keys(errors).length > 0) {
                setValidationErrors(errors);
                return;
            }

            setValidationErrors({});
            setStep(4);
        }
    };

    const handlePreviousStep = () => {
        if (step > 1) {
            setStep(step - 1);
        }
    };

    const handleSubmit = (e: FormEvent) => {
        e.preventDefault();
        post(`/paid-events/${paidEvent.slug}/register/submit`);
    };

    const StepIndicator = () => (
        <div className="mb-8">
            <div className="flex items-center justify-between">
                {[1, 2, 3, 4].map((s) => (
                    <div key={s} className="flex flex-1 items-center">
                        <div
                            className={`flex h-10 w-10 items-center justify-center rounded-full border-2 font-semibold transition-colors ${
                                s === step
                                    ? 'border-blue-600 bg-blue-600 text-white dark:border-blue-500 dark:bg-blue-500'
                                    : s < step
                                      ? 'border-green-500 bg-green-500 text-white dark:border-green-400 dark:bg-green-400'
                                      : 'border-slate-300 bg-white text-slate-400 dark:border-slate-600 dark:bg-slate-800 dark:text-slate-500'
                            }`}
                        >
                            {s < step ? <CheckCircle2 className="h-5 w-5" /> : s}
                        </div>
                        {s < 4 && (
                            <div
                                className={`mx-2 h-1 flex-1 rounded transition-colors ${
                                    s < step
                                        ? 'bg-green-500 dark:bg-green-400'
                                        : 'bg-slate-200 dark:bg-slate-700'
                                }`}
                            />
                        )}
                    </div>
                ))}
            </div>
            <div className="mt-3 flex justify-between text-xs font-medium">
                <span className={step === 1 ? 'text-blue-600 dark:text-blue-400' : 'text-slate-600 dark:text-slate-400'}>
                    Student ID
                </span>
                <span className={step === 2 ? 'text-blue-600 dark:text-blue-400' : 'text-slate-600 dark:text-slate-400'}>
                    Details
                </span>
                <span className={step === 3 ? 'text-blue-600 dark:text-blue-400' : 'text-slate-600 dark:text-slate-400'}>
                    Transport
                </span>
                <span className={step === 4 ? 'text-blue-600 dark:text-blue-400' : 'text-slate-600 dark:text-slate-400'}>
                    Review
                </span>
            </div>
        </div>
    );

    return (
        <MainLayout>
            <Head title={`Register for ${paidEvent.title}`} />

            <section className="container mx-auto px-4 py-8">
                {/* Back Button */}
                <Button
                    variant="ghost"
                    size="sm"
                    className="mb-4 gap-2"
                    onClick={() => router.visit(`/paid-events/${paidEvent.slug}`)}
                >
                    <ArrowLeft className="h-4 w-4" />
                    Back to Event
                </Button>

                {/* Header */}
                <div className="mb-8">
                    <h1 className="mb-2 text-2xl font-bold text-slate-900 sm:text-3xl dark:text-white">
                        Register for {paidEvent.title}
                    </h1>
                    <div className="flex flex-wrap items-center gap-2">
                        <Badge variant="outline" className="border-slate-200 bg-slate-50 dark:border-slate-700 dark:bg-slate-800">
                            📚 {paidEvent.semester}
                        </Badge>
                        <Badge variant="outline" className="border-blue-200 bg-blue-50 dark:border-blue-700 dark:bg-blue-900/30">
                            💰 ৳{paidEvent.registration_fee}
                        </Badge>
                    </div>
                </div>

                {/* Form Container */}
                <div className="mx-auto max-w-5xl">
                    <div className="rounded-xl border border-slate-200 bg-white p-6 shadow-sm sm:p-8 dark:border-slate-700 dark:bg-slate-900">
                        <StepIndicator />

                        <form onSubmit={handleSubmit}>
                            {/* Step 1: Student ID */}
                            {step === 1 && (
                                <div className="space-y-6">
                                    <div>
                                        <h2 className="mb-4 text-xl font-semibold text-slate-900 dark:text-white">
                                            Verify Your Student ID
                                        </h2>

                                        {paidEvent.student_id_rules && (
                                            <div className="mb-4 rounded-lg border border-blue-200 bg-blue-50 p-4 dark:border-blue-800/30 dark:bg-blue-900/20">
                                                <div className="mb-1 flex items-center gap-2 font-medium text-blue-900 dark:text-blue-300">
                                                    <AlertCircle className="h-4 w-4" />
                                                    Eligibility Requirements
                                                </div>
                                                <p className="text-sm text-blue-800 dark:text-blue-300">
                                                    {paidEvent.student_id_rules_guide ||
                                                        'Your student ID must match the eligibility criteria for this event.'}
                                                </p>
                                            </div>
                                        )}

                                        <div className="space-y-2">
                                            <Label htmlFor="student_id" className="text-slate-700 dark:text-slate-300">Student ID *</Label>
                                            <Input
                                                id="student_id"
                                                type="text"
                                                value={data.student_id}
                                                onChange={(e) => setData('student_id', e.target.value)}
                                                placeholder="Enter your student ID"
                                                className={studentIdError ? 'border-red-500' : ''}
                                            />
                                            {studentIdError && (
                                                <div className="flex items-center gap-2 rounded-lg border border-red-200 bg-red-50 p-3 text-sm text-red-600 dark:border-red-800/30 dark:bg-red-900/20 dark:text-red-400">
                                                    <AlertCircle className="h-4 w-4 shrink-0" />
                                                    {studentIdError}
                                                </div>
                                            )}
                                        </div>
                                    </div>
                                </div>
                            )}

                            {/* Step 2: Personal Details */}
                            {step === 2 && (
                                <div className="space-y-6">
                                    <h2 className="mb-4 text-xl font-semibold text-slate-900 dark:text-white">Personal Details</h2>

                                    <div className="grid gap-6 sm:grid-cols-2">
                                        <div className="space-y-2">
                                            <Label htmlFor="name" className="text-slate-700 dark:text-slate-300">Full Name *</Label>
                                            <Input
                                                id="name"
                                                type="text"
                                                value={data.name}
                                                onChange={(e) => setData('name', e.target.value)}
                                                placeholder="Enter your full name"
                                                className={validationErrors.name ? 'border-red-500' : ''}
                                            />
                                            {(validationErrors.name || errors.name) && (
                                                <p className="text-sm text-red-600 dark:text-red-400">{validationErrors.name || errors.name}</p>
                                            )}
                                        </div>

                                        <div className="space-y-2">
                                            <Label htmlFor="email" className="text-slate-700 dark:text-slate-300">Email *</Label>
                                            <Input 
                                                id="email" 
                                                type="email" 
                                                value={user.email} 
                                                disabled 
                                                className="cursor-not-allowed bg-slate-100 dark:bg-slate-800" 
                                            />
                                            <p className="text-xs text-slate-500 dark:text-slate-400">Email cannot be changed</p>
                                        </div>

                                        <div className="space-y-2">
                                            <Label htmlFor="phone" className="text-slate-700 dark:text-slate-300">Phone Number *</Label>
                                            <Input
                                                id="phone"
                                                type="tel"
                                                value={data.phone}
                                                onChange={(e) => setData('phone', e.target.value)}
                                                placeholder="+880 1XXXXXXXXX"
                                                className={validationErrors.phone ? 'border-red-500' : ''}
                                            />
                                            {(validationErrors.phone || errors.phone) && (
                                                <p className="text-sm text-red-600 dark:text-red-400">{validationErrors.phone || errors.phone}</p>
                                            )}
                                        </div>

                                        <div className="space-y-2">
                                            <Label htmlFor="gender" className="text-slate-700 dark:text-slate-300">Gender *</Label>
                                            <Select value={data.gender} onValueChange={(value) => setData('gender', value)}>
                                                <SelectTrigger className={validationErrors.gender ? 'border-red-500 w-full' : 'w-full'}>
                                                    <SelectValue placeholder="Select gender" />
                                                </SelectTrigger>
                                                <SelectContent>
                                                    {genders.map((g) => (
                                                        <SelectItem key={g.value} value={g.value}>
                                                            {g.label}
                                                        </SelectItem>
                                                    ))}
                                                </SelectContent>
                                            </Select>
                                            {(validationErrors.gender || errors.gender) && (
                                                <p className="text-sm text-red-600 dark:text-red-400">{validationErrors.gender || errors.gender}</p>
                                            )}
                                        </div>

                                        <div className="space-y-2">
                                            <Label htmlFor="department" className="text-slate-700 dark:text-slate-300">Department *</Label>
                                            {paidEvent.departments && paidEvent.departments.length > 0 ? (
                                                <Select value={data.department} onValueChange={(value) => setData('department', value)}>
                                                    <SelectTrigger className={validationErrors.department ? 'border-red-500 w-full' : 'w-full'}>
                                                        <SelectValue placeholder="Select department" />
                                                    </SelectTrigger>
                                                    <SelectContent>
                                                        {paidEvent.departments.map((dept, idx) => (
                                                            <SelectItem key={`dept-${idx}`} value={dept.name}>
                                                                {dept.name}
                                                            </SelectItem>
                                                        ))}
                                                    </SelectContent>
                                                </Select>
                                            ) : (
                                                <div className="rounded-lg border border-orange-200 bg-orange-50 p-3 text-sm text-orange-800 dark:border-orange-800/30 dark:bg-orange-900/20 dark:text-orange-300">
                                                    <p className="font-medium">No departments configured</p>
                                                    <p className="mt-1">Please contact the event organizer to add department options.</p>
                                                </div>
                                            )}
                                            {(validationErrors.department || errors.department) && (
                                                <p className="text-sm text-red-600 dark:text-red-400">{validationErrors.department || errors.department}</p>
                                            )}
                                        </div>

                                        <div className="space-y-2">
                                            <Label htmlFor="section" className="text-slate-700 dark:text-slate-300">Section *</Label>
                                            {paidEvent.sections && paidEvent.sections.length > 0 ? (
                                                <Select value={data.section} onValueChange={(value) => setData('section', value)}>
                                                    <SelectTrigger className={validationErrors.section ? 'border-red-500 w-full' : 'w-full'}>
                                                        <SelectValue placeholder="Select section" />
                                                    </SelectTrigger>
                                                    <SelectContent>
                                                        {paidEvent.sections.map((sec, idx) => (
                                                            <SelectItem key={`sec-${idx}`} value={sec.name}>
                                                                {sec.name}
                                                            </SelectItem>
                                                        ))}
                                                    </SelectContent>
                                                </Select>
                                            ) : (
                                                <div className="rounded-lg border border-orange-200 bg-orange-50 p-3 text-sm text-orange-800 dark:border-orange-800/30 dark:bg-orange-900/20 dark:text-orange-300">
                                                    <p className="font-medium">No sections configured</p>
                                                    <p className="mt-1">Please contact the event organizer to add section options.</p>
                                                </div>
                                            )}
                                            {(validationErrors.section || errors.section) && (
                                                <p className="text-sm text-red-600 dark:text-red-400">{validationErrors.section || errors.section}</p>
                                            )}
                                        </div>

                                        <div className="space-y-2">
                                            <Label htmlFor="lab_teacher_name" className="text-slate-700 dark:text-slate-300">Lab Teacher *</Label>
                                            {paidEvent.lab_teacher_names && paidEvent.lab_teacher_names.length > 0 ? (
                                                <Select value={data.lab_teacher_name} onValueChange={(value) => setData('lab_teacher_name', value)}>
                                                    <SelectTrigger className={validationErrors.lab_teacher_name ? 'border-red-500 w-full' : 'w-full'}>
                                                        <SelectValue placeholder="Select lab teacher" />
                                                    </SelectTrigger>
                                                    <SelectContent>
                                                        {paidEvent.lab_teacher_names.map((teacher, idx) => (
                                                            <SelectItem key={`teacher-${idx}`} value={teacher.full_name}>
                                                                {teacher.full_name} ({teacher.initial})
                                                            </SelectItem>
                                                        ))}
                                                    </SelectContent>
                                                </Select>
                                            ) : (
                                                <div className="rounded-lg border border-orange-200 bg-orange-50 p-3 text-sm text-orange-800 dark:border-orange-800/30 dark:bg-orange-900/20 dark:text-orange-300">
                                                    <p className="font-medium">No lab teachers configured</p>
                                                    <p className="mt-1">Please contact the event organizer to add lab teacher options.</p>
                                                </div>
                                            )}
                                            {(validationErrors.lab_teacher_name || errors.lab_teacher_name) && (
                                                <p className="text-sm text-red-600 dark:text-red-400">{validationErrors.lab_teacher_name || errors.lab_teacher_name}</p>
                                            )}
                                        </div>

                                        <div className="space-y-2">
                                            <Label htmlFor="tshirt_size" className="text-slate-700 dark:text-slate-300">T-shirt Size *</Label>
                                            {tshirtSizes && tshirtSizes.length > 0 ? (
                                                <Select value={data.tshirt_size} onValueChange={(value) => setData('tshirt_size', value)}>
                                                    <SelectTrigger className={validationErrors.tshirt_size ? 'border-red-500 w-full' : 'w-full'}>
                                                        <SelectValue placeholder="Select size" />
                                                    </SelectTrigger>
                                                    <SelectContent>
                                                        {tshirtSizes.map((size) => (
                                                            <SelectItem key={size.value} value={size.value}>
                                                                {size.label}
                                                            </SelectItem>
                                                        ))}
                                                    </SelectContent>
                                                </Select>
                                            ) : (
                                                <div className="rounded-lg border border-orange-200 bg-orange-50 p-3 text-sm text-orange-800 dark:border-orange-800/30 dark:bg-orange-900/20 dark:text-orange-300">
                                                    <p className="font-medium">No t-shirt sizes configured</p>
                                                    <p className="mt-1">Please contact the event organizer to add t-shirt size options.</p>
                                                </div>
                                            )}
                                            {(validationErrors.tshirt_size || errors.tshirt_size) && (
                                                <p className="text-sm text-red-600 dark:text-red-400">{validationErrors.tshirt_size || errors.tshirt_size}</p>
                                            )}
                                        </div>
                                    </div>

                                    {/* T-shirt Size Guideline */}
                                    {paidEvent.tshirt_guideline_url && (
                                        <div className="space-y-2">
                                            <Label className="text-slate-700 dark:text-slate-300">T-shirt Size Guide</Label>
                                            <div className="overflow-hidden rounded-lg border border-slate-200 dark:border-slate-700">
                                                <img 
                                                    src={paidEvent.tshirt_guideline_url} 
                                                    alt="T-shirt Size Guide" 
                                                    className="h-auto w-full object-contain"
                                                />
                                            </div>
                                        </div>
                                    )}
                                </div>
                            )}

                            {/* Step 3: Transport */}
                            {step === 3 && (
                                <div className="space-y-6">
                                    <h2 className="mb-4 text-xl font-semibold text-slate-900 dark:text-white">Transport Service</h2>

                                    <div className="space-y-4">
                                        <div className="flex items-start gap-3 rounded-lg border border-slate-200 bg-slate-50 p-4 dark:border-slate-700 dark:bg-slate-800/50">
                                            <input
                                                type="checkbox"
                                                id="transport_service_required"
                                                checked={data.transport_service_required}
                                                onChange={(e) => {
                                                    setData('transport_service_required', e.target.checked);
                                                    if (!e.target.checked) {
                                                        setData('pickup_point', '');
                                                    }
                                                }}
                                                className="mt-1 h-4 w-4 rounded border-slate-300 text-blue-600 focus:ring-blue-500 dark:border-slate-600"
                                            />
                                            <div className="flex-1">
                                                <Label htmlFor="transport_service_required" className="cursor-pointer font-medium text-slate-900 dark:text-white">
                                                    I need transport service
                                                </Label>
                                                <p className="mt-1 text-sm text-slate-600 dark:text-slate-400">
                                                    Check this if you would like to use the provided transport service
                                                </p>
                                            </div>
                                        </div>

                                        {data.transport_service_required && (
                                            <div className="space-y-2">
                                                <Label htmlFor="pickup_point" className="text-slate-700 dark:text-slate-300">Pickup Point *</Label>
                                                {paidEvent.pickup_points && paidEvent.pickup_points.length > 0 ? (
                                                    <Select value={data.pickup_point} onValueChange={(value) => setData('pickup_point', value)}>
                                                        <SelectTrigger className={validationErrors.pickup_point ? 'border-red-500 w-full' : 'w-full'}>
                                                            <SelectValue placeholder="Select pickup point" />
                                                        </SelectTrigger>
                                                        <SelectContent>
                                                            {paidEvent.pickup_points.map((point, idx) => (
                                                                <SelectItem key={`pickup-${idx}`} value={point.name}>
                                                                    📍 {point.name}
                                                                </SelectItem>
                                                            ))}
                                                        </SelectContent>
                                                    </Select>
                                                ) : (
                                                    <div className="rounded-lg border border-orange-200 bg-orange-50 p-3 text-sm text-orange-800 dark:border-orange-800/30 dark:bg-orange-900/20 dark:text-orange-300">
                                                        <p className="font-medium">No pickup points configured</p>
                                                        <p className="mt-1">Please contact the event organizer to add pickup point options.</p>
                                                    </div>
                                                )}
                                                {(validationErrors.pickup_point || errors.pickup_point) && (
                                                    <p className="text-sm text-red-600 dark:text-red-400">{validationErrors.pickup_point || errors.pickup_point}</p>
                                                )}
                                            </div>
                                        )}
                                    </div>
                                </div>
                            )}

                            {/* Step 4: Review */}
                            {step === 4 && (
                                <div className="space-y-6">
                                    <h2 className="mb-4 text-xl font-semibold text-slate-900 dark:text-white">Review Your Registration</h2>

                                    <div className="space-y-4 rounded-lg border border-slate-200 bg-slate-50 p-4 dark:border-slate-700 dark:bg-slate-800/50">
                                        <div className="grid gap-4 sm:grid-cols-2">
                                            <div>
                                                <div className="text-xs font-medium text-slate-500 dark:text-slate-400">Student ID</div>
                                                <div className="mt-1 font-medium text-slate-900 dark:text-white">{data.student_id}</div>
                                            </div>
                                            <div>
                                                <div className="text-xs font-medium text-slate-500 dark:text-slate-400">Full Name</div>
                                                <div className="mt-1 font-medium text-slate-900 dark:text-white">{data.name}</div>
                                            </div>
                                            <div>
                                                <div className="text-xs font-medium text-slate-500 dark:text-slate-400">Email</div>
                                                <div className="mt-1 font-medium text-slate-900 dark:text-white">{user.email}</div>
                                            </div>
                                            <div>
                                                <div className="text-xs font-medium text-slate-500 dark:text-slate-400">Phone</div>
                                                <div className="mt-1 font-medium text-slate-900 dark:text-white">{data.phone}</div>
                                            </div>
                                            <div>
                                                <div className="text-xs font-medium text-slate-500 dark:text-slate-400">Department</div>
                                                <div className="mt-1 font-medium text-slate-900 dark:text-white">{data.department}</div>
                                            </div>
                                            {data.section && (
                                                <div>
                                                    <div className="text-xs font-medium text-slate-500 dark:text-slate-400">Section</div>
                                                    <div className="mt-1 font-medium text-slate-900 dark:text-white">{data.section}</div>
                                                </div>
                                            )}
                                            <div>
                                                <div className="text-xs font-medium text-slate-500 dark:text-slate-400">Gender</div>
                                                <div className="mt-1 font-medium capitalize text-slate-900 dark:text-white">{data.gender}</div>
                                            </div>
                                            {data.lab_teacher_name && (
                                                <div>
                                                    <div className="text-xs font-medium text-slate-500 dark:text-slate-400">Lab Teacher</div>
                                                    <div className="mt-1 font-medium text-slate-900 dark:text-white">{data.lab_teacher_name}</div>
                                                </div>
                                            )}
                                            <div>
                                                <div className="text-xs font-medium text-slate-500 dark:text-slate-400">T-shirt Size</div>
                                                <div className="mt-1 font-medium uppercase text-slate-900 dark:text-white">{data.tshirt_size}</div>
                                            </div>
                                            <div>
                                                <div className="text-xs font-medium text-slate-500 dark:text-slate-400">Transport Service</div>
                                                <div className="mt-1 font-medium text-slate-900 dark:text-white">
                                                    {data.transport_service_required ? 'Yes' : 'No'}
                                                </div>
                                            </div>
                                            {data.transport_service_required && data.pickup_point && (
                                                <div className="sm:col-span-2">
                                                    <div className="text-xs font-medium text-slate-500 dark:text-slate-400">Pickup Point</div>
                                                    <div className="mt-1 font-medium text-slate-900 dark:text-white">📍 {data.pickup_point}</div>
                                                </div>
                                            )}
                                        </div>

                                        <div className="mt-4 border-t border-slate-200 pt-4 dark:border-slate-700">
                                            <div className="flex items-center justify-between">
                                                <span className="text-lg font-semibold text-slate-900 dark:text-white">Registration Fee</span>
                                                <span className="text-2xl font-bold text-blue-600 dark:text-blue-400">
                                                    ৳{paidEvent.registration_fee}
                                                </span>
                                            </div>
                                        </div>
                                    </div>

                                    <div className="rounded-lg border border-yellow-200 bg-yellow-50 p-4 dark:border-yellow-800/30 dark:bg-yellow-900/20">
                                        <div className="flex gap-3">
                                            <AlertCircle className="mt-0.5 h-5 w-5 shrink-0 text-yellow-600 dark:text-yellow-500" />
                                            <div className="text-sm text-yellow-800 dark:text-yellow-300">
                                                <p className="font-medium">Payment Required</p>
                                                <p className="mt-1">
                                                    After submitting your registration, you'll need to complete the payment to confirm your spot.
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            )}

                            {/* Navigation Buttons */}
                            <div className="mt-8 flex items-center justify-between gap-4">
                                <Button
                                    type="button"
                                    variant="outline"
                                    onClick={handlePreviousStep}
                                    disabled={step === 1 || processing}
                                    className="gap-2 border-slate-300 dark:border-slate-600"
                                >
                                    <ArrowLeft className="h-4 w-4" />
                                    Previous
                                </Button>

                                {step < 4 ? (
                                    <Button
                                        type="button"
                                        onClick={handleNextStep}
                                        disabled={isValidatingStudentId || processing}
                                        className="gap-2 bg-gradient-to-r from-blue-600 to-cyan-600 hover:from-blue-700 hover:to-cyan-700 dark:from-blue-500 dark:to-cyan-500 dark:hover:from-blue-600 dark:hover:to-cyan-600"
                                    >
                                        {isValidatingStudentId ? (
                                            <>
                                                <Loader2 className="h-4 w-4 animate-spin" />
                                                Validating...
                                            </>
                                        ) : (
                                            <>
                                                Next
                                                <ArrowRight className="h-4 w-4" />
                                            </>
                                        )}
                                    </Button>
                                ) : (
                                    <Button 
                                        type="submit" 
                                        disabled={processing} 
                                        className="gap-2 bg-gradient-to-r from-blue-600 to-cyan-600 hover:from-blue-700 hover:to-cyan-700 dark:from-blue-500 dark:to-cyan-500 dark:hover:from-blue-600 dark:hover:to-cyan-600"
                                    >
                                        {processing ? (
                                            <>
                                                <Loader2 className="h-4 w-4 animate-spin" />
                                                Submitting...
                                            </>
                                        ) : (
                                            <>
                                                <ShoppingCart className="h-4 w-4" />
                                                Submit & Pay
                                            </>
                                        )}
                                    </Button>
                                )}
                            </div>
                        </form>
                    </div>
                </div>
            </section>
        </MainLayout>
    );
}
